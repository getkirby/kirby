<?php

declare(strict_types = 1);

namespace Darkroom\Drivers;

use Darkroom\Driver;
use Darkroom\Exception\LimitException;
use Darkroom\Exception\ProcessException;
use Darkroom\Format;
use Darkroom\Orientation;
use Darkroom\Plan;
use Darkroom\Settings;
use Darkroom\Size;
use Darkroom\Source;
use Darkroom\Tools\Quality;
use Darkroom\Tools\Sharpen;
use Imagick as Image;
use ImagickException;
use ImagickKernel;

final class Imagick extends Driver
{
	/**
	 * ImageMagick's codes for a resource limit error
	 * and for a pixel cache it can't allocate
	 */
	protected const array LIMITS = [400, 445];

	protected function animates(Format $format): bool
	{
		$name  = static::name($format);
		$image = new Image();

		try {
			$image->newImage(1, 1, 'white', $name);
			$image->newImage(1, 1, 'black', $name);

			// without a delay, JPEG XL frames are layers of one image
			foreach ($image as $frame) {
				$frame->setImageDelay(10);
			}

			$blob   = $image->getImagesBlob();
			$read   = new Image();
			$delays = [];

			$read->readImageBlob($blob);

			foreach ($read as $frame) {
				$delays[] = $frame->getImageDelay();
			}

			return $delays === [10, 10];
		} catch (ImagickException) {
			return false;
		}
	}

	protected static function cmyk(Image $frame): void
	{
		$profile = static::profile($frame);

		// the profile's color space, after its size,
		// CMM, version and class
		if (substr($profile, 16, 4) === 'CMYK') {
			$frame->profileImage('icc', static::srgb());
		}

		if ($frame->getImageColorspace() === Image::COLORSPACE_CMYK) {
			$frame->transformImageColorspace(Image::COLORSPACE_SRGB);
		}
	}

	protected static function decode(Plan $plan): Image
	{
		$size  = $plan->jpegSize();
		$name  = static::name($plan->source->format());
		$scene = $plan->frames === true ? '' : '[0]';
		$image = new Image();

		$image->setOption('jpeg:size', $size->width . 'x' . $size->height);

		// a HEIF sequence's still, which the source's size
		// describes, not the first frame of its track
		$image->setOption('heic:primary-image', 'true');

		// the prefix keeps ImageMagick from guessing the format
		$image->readImage($name . ':' . $plan->source->file . $scene);

		$page = $image->getImagePage();
		$full = [
			'width'  => $image->getImageWidth(),
			'height' => $image->getImageHeight(),
			'x'      => 0,
			'y'      => 0,
		];

		// coalescing copies even a frame that fills its screen
		if ($image->getNumberImages() === 1 && $page === $full) {
			return $image;
		}

		return $image->coalesceImages();
	}

	/**
	 * The sample where libheif loads the decoder
	 * as a plugin, else whether there is a coder
	 */
	protected function decodes(Format $format): bool
	{
		$image  = new Image();
		$sample = self::SAMPLES[$format->name] ?? null;

		try {
			if ($sample === null) {
				return $image->setFormat(static::name($format));
			}

			return $image->readImageBlob(base64_decode($sample));
		} catch (ImagickException) {
			return false;
		}
	}

	protected function defaults(): array
	{
		return [
			'limit'   => Settings::LIMIT,
			'threads' => 1,
		];
	}

	protected static function encode(
		Image $image,
		Plan $plan,
		string $file
	): void {
		// GIF stores only what changes, other encoders need whole frames;
		// unchanged pixels turn transparent, so their dithering doesn't shimmer
		if (
			$plan->frames === true &&
			$plan->format === Format::Gif
		) {
			$image = $image->optimizeImageLayers();
			$image->optimizeImageTransparency();
		}

		[$quality, $whole] = Quality::for($plan->format, $plan->quality);

		// JPEG and WebP read the image's quality,
		// AVIF and JPEG XL the wand's
		$image->setCompressionQuality($quality);
		$image->setInterlaceScheme($plan->interlace ? Image::INTERLACE_PLANE : Image::INTERLACE_NO);

		// chroma whole or at half resolution both ways (4:2:0),
		// where ImageMagick would keep the source's or,
		// from quality 90 on, write it whole
		if ($plan->format === Format::Jpeg) {
			$image->setOption(
				'jpeg:sampling-factor',
				$whole === true ? '1x1,1x1,1x1' : '2x2,1x1,1x1'
			);
		}

		if (
			$plan->format === Format::Avif ||
			$plan->format === Format::Heic
		) {
			$image->setOption(
				'heic:chroma',
				$whole === true ? '444' : '420'
			);
		}

		// zlib's default, where PNG would read
		// the quality's tens as the level
		if ($plan->format === Format::Png) {
			$image->setOption('png:compression-level', '6');
		}

		foreach ($image as $frame) {
			$frame->setImageCompressionQuality($quality);

			// 16 bits only make thumbs larger
			if ($plan->format === Format::Png) {
				$frame->setImageDepth(8);
			}

			// on white, as encoders without alpha
			// would keep the colors under it
			if ($plan->format->isOpaque() === true) {
				$frame->setImageBackgroundColor('white');
				$frame->setImageAlphaChannel(Image::ALPHACHANNEL_REMOVE);
			}
		}

		// the prefix decides the format,
		// not the temp file's name
		$image->writeImages(
			static::name($plan->format) . ':' . $file,
			true
		);
	}

	protected function encodes(Format $format): bool
	{
		$image = new Image();

		try {
			$image->newImage(1, 1, 'white', static::name($format));

			return $image->getImageBlob() !== '';
		} catch (ImagickException) {
			return false;
		}
	}

	protected static function filter(Image $frame, Plan $plan): void
	{
		if ($plan->options->grayscale === true) {
			static::gray($frame);
		}

		if ($plan->options->blur !== null) {
			$frame->blurImage(0, $plan->options->blur);
		}

		// the kernel the other drivers use
		if ($plan->options->sharpen !== null) {
			$matrix = Sharpen::kernel($plan->options->sharpen);
			$kernel = ImagickKernel::fromMatrix($matrix);

			$frame->morphology(Image::MORPHOLOGY_CONVOLVE, 1, $kernel);
		}
	}

	protected function frame(Image $frame, Plan $plan): void
	{
		$resample = $plan->resample();
		$decoded  = new Size(
			width:  $frame->getImageWidth(),
			height: $frame->getImageHeight()
		);

		$region = $plan->region($decoded);

		// cropping copies even the whole frame
		if (
			$region->width < $decoded->width ||
			$region->height < $decoded->height
		) {
			$frame->cropImage(
				$region->width,
				$region->height,
				$region->x,
				$region->y
			);

			// the crop would keep its offset into the frame
			$frame->setImagePage(0, 0, 0, 0);
		}

		// ImageMagick's filter: Lanczos,
		// Mitchell for alpha and palettes
		$frame->resizeImage(
			$resample->width,
			$resample->height,
			Image::FILTER_UNDEFINED,
			1
		);

		// web formats show no CMYK
		if ($frame->getImageColorspace() === Image::COLORSPACE_CMYK) {
			static::cmyk($frame);
		}

		static::orient($frame, $plan->orientation);
		static::filter($frame, $plan);
		static::strip($frame);
	}

	protected static function gray(Image $frame): void
	{
		$luma = [...self::LUMA, 0.0, 0.0];

		$frame->colorMatrixImage([
			...$luma,
			...$luma,
			...$luma,
			0.0, 0.0, 0.0, 1.0, 0.0,
			0.0, 0.0, 0.0, 0.0, 1.0,
		]);

		$frame->transformImageColorspace(Image::COLORSPACE_GRAY);
	}

	public function info(): array
	{
		if ($this->isAvailable() === false) {
			return parent::info();
		}

		// `ImageMagick 7.1.2-31 Q16-HDRI …`
		$version = explode(' ', Image::getVersion()['versionString']);

		return [
			'library' => 'ImageMagick',
			'version' => $version[1],
		];
	}

	public function isAvailable(): bool
	{
		return
			extension_loaded('imagick') === true &&
			defined(Image::class . '::RESOURCETYPE_LISTLENGTH') === true;
	}

	/**
	 * @param array<int, int> $limits
	 * @return array<int, int>
	 */
	protected static function limit(array $limits): array
	{
		$previous = [];

		foreach ($limits as $type => $value) {
			$limit = Image::getResourceLimit($type);

			$previous[$type] = $limit < PHP_INT_MAX ? (int)$limit : PHP_INT_MAX;

			Image::setResourceLimit($type, $value);
		}

		return $previous;
	}

	/**
	 * @return array<int, int>
	 */
	protected function limits(Plan $plan): array
	{
		$limits = [
			Image::RESOURCETYPE_THREAD => (int)$this->settings['threads']
		];

		/** @var int|null $limit */
		$limit = $this->settings['limit'];

		if ($limit === null) {
			return $limits;
		}

		$source = $plan->source->size();
		$pixels = $source->pixels();

		// libheif decodes the coded image, larger than the clean
		// aperture, within the square of the shorter side limit
		$side = max(
			$source->width,
			$source->height,
			(int)sqrt($limit)
		);

		return $limits + [
			Image::RESOURCETYPE_WIDTH      => $side,
			Image::RESOURCETYPE_HEIGHT     => $side,
			// a larger frame goes to the disk, within its limit
			Image::RESOURCETYPE_AREA       => $limit + 1,
			// ImageMagick refuses a list as long as its limit
			Image::RESOURCETYPE_LISTLENGTH => intdiv($limit, $pixels) + 1,
			// spilled onto the disk, the pixel cache
			// holds `limit` pixels of four 32-bit channels
			Image::RESOURCETYPE_DISK       => $limit * 16,
		];
	}

	protected static function name(Format $format): string
	{
		return strtoupper($format->name);
	}

	protected static function orient(
		Image $frame,
		Orientation $orientation
	): void {
		$turns = $orientation->turns();

		if ($turns > 0) {
			$frame->rotateImage('none', 90 * $turns);
		}

		if ($orientation->isFlipped() === true) {
			$frame->flopImage();
		}

		$frame->setImageOrientation(Image::ORIENTATION_TOPLEFT);
	}

	public function orients(Source $source): bool
	{
		return match ($source->format()) {
			Format::Avif,
			Format::Heic => true,
			default      => false
		};
	}

	/**
	 * @throws LimitException If the image exceeds ImageMagick's resource limits
	 * @throws ProcessException If ImageMagick fails
	 */
	public function process(Plan $plan, string $file): void
	{
		$limits   = $this->limits($plan);
		$previous = static::limit($limits);
		$time     = (int)ini_get('max_execution_time');

		// PHP's timer doesn't stop ImageMagick;
		// left set, as no later image of the request
		// may take longer, and PHP_INT_MAX overflows its clock
		if ($time > 0) {
			Image::setResourceLimit(Image::RESOURCETYPE_TIME, $time);
		}

		try {
			$image = static::decode($plan);

			foreach ($image as $frame) {
				$this->frame($frame, $plan);
			}

			static::encode($image, $plan, $file);
		} catch (ImagickException $exception) {
			$message = 'ImageMagick failed on ' . $plan->source->file . ': ' . $exception->getMessage();

			if (in_array($exception->getCode(), self::LIMITS, true) === true) {
				throw new LimitException(
					message: $message,
					previous: $exception
				);
			}

			throw new ProcessException(
				message: $message,
				previous: $exception
			);
		} finally {
			static::limit($previous);
		}
	}

	protected static function profile(Image $frame): string
	{
		return (string)($frame->getImageProfiles('icc')['icc'] ?? '');
	}

	/**
	 * sRGB-v2-micro of the Compact ICC Profiles (CC0)
	 */
	protected static function srgb(): string
	{
		return (string)file_get_contents(dirname(__DIR__, 2) . '/assets/srgb.icc');
	}

	/**
	 * Without meta but the color profile
	 */
	protected static function strip(Image $frame): void
	{
		foreach ($frame->getImageProfiles('*', false) as $name) {
			if ($name !== 'icc') {
				$frame->removeImageProfile((string)$name);
			}
		}

		foreach ($frame->getImageProperties('*', false) as $name) {
			$frame->deleteImageProperty($name);
		}
	}
}
