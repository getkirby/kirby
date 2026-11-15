<?php

declare(strict_types = 1);

namespace Darkroom\Drivers;

use Darkroom\Driver;
use Darkroom\Exception\LimitException;
use Darkroom\Exception\ProcessException;
use Darkroom\Format;
use Darkroom\Orientation;
use Darkroom\Plan;
use Darkroom\Rect;
use Darkroom\Size;
use Darkroom\Source;
use Darkroom\Tools\Quality;
use Darkroom\Tools\Sharpen;
use ErrorException;
use GdImage;
use ValueError;

final class Gd extends Driver
{
	/**
	 * Transparent black, as GD stores it in truecolor images
	 */
	protected const int CLEAR = 0x7F000000;

	protected static function blank(int $width, int $height): GdImage
	{
		$image = imagecreatetruecolor($width, $height);

		if ($image === false) {
			throw new ProcessException(
				message: 'GD can\'t create a ' . $width . '×' . $height . ' image'
			);
		}

		imagealphablending($image, enable: false);

		return $image;
	}

	protected static function blur(GdImage $image, int $sigma): GdImage
	{
		$width    = imagesx($image);
		$height   = imagesy($image);
		$halvings = (int)log($sigma, 2);
		$factor   = 1 << $halvings;
		$area     = $factor * $factor;

		// shrinking and doubling back add a variance of
		// (area - 1) / 3, each pass 1/2 at its scale
		$passes = (int)round((6 * $sigma * $sigma - 2 * $area + 2) / (3 * $area));

		for ($level = $halvings; $level >= 0; $level--) {
			$factor = 1 << $level;
			$rect   = new Rect(0, 0, imagesx($image), imagesy($image));
			$size   = new Size(
				width:  (int)ceil($width / $factor),
				height: (int)ceil($height / $factor)
			);
			$image  = static::resize($image, $rect, $size);

			static::smooth($image, $level === $halvings ? $passes : 1);
		}

		return $image;
	}

	/**
	 * Decodes the first frame
	 */
	protected static function decode(Plan $plan): GdImage
	{
		$file    = $plan->source->file;
		$decoder = static::decoder($plan->source->format());
		$image   = $decoder($file);

		if ($image instanceof GdImage === false) {
			throw new ProcessException(
				message: 'GD can\'t decode: ' . $file
			);
		}

		return $image;
	}

	protected static function decoder(Format $format): string
	{
		return 'imagecreatefrom' . strtolower($format->name);
	}

	protected function decodes(Format $format): bool
	{
		$decoder = static::decoder($format);

		return function_exists($decoder);
	}

	/**
	 * Whether GD decodes an animated WebP to its first frame
	 */
	protected static function decodesAnimation(): bool
	{
		$pixel = 'VP8L' . pack('V', 14) . "\x2F\0\0\0\0\x07\x10\x11\xFD\x0F\x44\x44\xFF\x03";
		$frame = 'ANMF' . pack('V', 16 + strlen($pixel)) . str_repeat("\0", 12) . "\x64\0\0\0" . $pixel;
		$webp  =
			'WEBP' .
			'VP8X' . pack('V', 10) . "\x02" . str_repeat("\0", 9) .
			'ANIM' . pack('V', 6) . str_repeat("\0", 6) .
			$frame . $frame;

		$riff = 'RIFF' . pack('V', strlen($webp)) . $webp;

		return @imagecreatefromstring($riff) !== false;
	}

	protected function encode(
		GdImage $image,
		Plan $plan,
		string $file
	): void {
		// encoders without alpha would keep the colors under it; JPEG
		// decodes opaque, and no step after decoding adds transparency
		if (
			$plan->format->isOpaque() === true &&
			$plan->source->format()->isOpaque() === false
		) {
			$image = static::flatten($image);
		}

		imagesavealpha($image, true);

		// GIF knows one transparent color instead of alpha
		if ($plan->format === Format::Gif) {
			static::threshold($image);

			$image = static::palette($image);
		}

		imageinterlace($image, $plan->interlace);

		$encoder = static::encoder($plan->format);
		$options = match ($plan->format) {
			Format::Avif,
			Format::Jpeg,
			Format::Webp => [$this->quality($plan)],
			default      => []
		};

		if ($encoder($image, $file, ...$options) !== true) {
			throw new ProcessException(
				message: 'GD can\'t encode ' . $plan->format->extension()
			);
		}
	}

	protected static function encoder(Format $format): string
	{
		return 'image' . strtolower($format->name);
	}

	protected function encodes(Format $format): bool
	{
		$encoder = static::encoder($format);

		if (function_exists($encoder) === false) {
			return false;
		}

		$image  = imagecreatetruecolor(1, 1);
		$stream = fopen('php://memory', 'w+b');

		return @$encoder($image, $stream) === true;
	}

	protected static function filter(
		GdImage $image,
		Plan $plan
	): GdImage {
		if ($plan->options->grayscale === true) {
			imagefilter($image, IMG_FILTER_GRAYSCALE);
		}

		if ($plan->options->blur !== null) {
			$image = static::blur($image, $plan->options->blur);
		}

		if ($plan->options->sharpen !== null) {
			static::sharpen($image, $plan->options->sharpen);
		}

		return $image;
	}

	protected static function flatten(GdImage $image): GdImage
	{
		$width     = imagesx($image);
		$height    = imagesy($image);
		$flattened = static::blank($width, $height);

		imagefilledrectangle($flattened, 0, 0, $width - 1, $height - 1, 0xFFFFFF);
		imagealphablending($flattened, true);
		imagecopy($flattened, $image, 0, 0, 0, 0, $width, $height);

		return $flattened;
	}

	/**
	 * The source's profile if it describes RGB colors,
	 * by the color space in its header
	 */
	public function icc(Plan $plan): string|null
	{
		$profile = $plan->source->icc();

		return substr($profile, 16, 4) === 'RGB ' ? $profile : null;
	}

	public function info(): array
	{
		if ($this->isAvailable() === false) {
			return parent::info();
		}

		return [
			'library' => 'GD',
			'version' => (string)gd_info()['GD Version'],
		];
	}

	public function isAvailable(): bool
	{
		return extension_loaded('gd');
	}

	protected static function orient(
		GdImage $image,
		Orientation $orientation
	): GdImage {
		$turns = $orientation->turns();

		// a half turn in place, as `imagerotate()` copies;
		// GD turns counterclockwise
		if ($turns === 2) {
			imageflip($image, IMG_FLIP_BOTH);
		} elseif ($turns > 0) {
			$image = imagerotate($image, -90 * $turns, self::CLEAR);

			if ($image === false) {
				throw new ProcessException(
					message: 'GD can\'t rotate the image'
				);
			}

			imagealphablending($image, false);
		}

		if ($orientation->isFlipped() === true) {
			imageflip($image, IMG_FLIP_HORIZONTAL);
		}

		return $image;
	}

	protected static function palette(GdImage $image): GdImage
	{
		$width   = imagesx($image);
		$height  = imagesy($image);
		$palette = static::blank($width, $height);

		imagecopy($palette, $image, 0, 0, 0, 0, $width, $height);
		imagecolortransparent($palette, self::CLEAR);
		imagetruecolortopalette($palette, true, 256);
		imagecolormatch($image, $palette);

		return $palette;
	}

	protected static function preflight(Plan $plan): void
	{
		/** @var non-empty-string $setting always set, -1 for no limit */
		$setting = ini_get('memory_limit');
		$limit   = ini_parse_quantity($setting);

		if ($limit <= 0) {
			return;
		}

		// four bytes a pixel: the source (twice for PNG, whose rows GD
		// reads whole before copying), the resampled image, a copy of it,
		// and the encoded image, at most about as large, twice while copied
		$resample = $plan->resample();
		$copies   = $plan->source->format() === Format::Png ? 2 : 1;
		$pixels   = $plan->source->size()->pixels() * $copies + $resample->pixels() * 4;

		if ($pixels * 4 > $limit - memory_get_usage()) {
			throw new LimitException(
				message: 'Image too large for the memory limit: ' . $plan->source->file
			);
		}
	}

	/**
	 * @throws LimitException Before decoding, if the image can't fit into the memory limit
	 * @throws ProcessException If GD fails
	 */
	public function process(Plan $plan, string $file): void
	{
		static::preflight($plan);

		// GD reports failures as warnings,
		// except libpng's, which it recovers from
		set_error_handler(static function (int $level, string $message): bool {
			if (str_contains($message, 'libpng warning: ') === true) {
				return true;
			}

			throw new ErrorException($message, 0, $level);
		}, E_WARNING | E_NOTICE);

		try {
			$image = static::decode($plan);
			$image = static::resample($image, $plan);
			$image = static::orient($image, $plan->orientation);
			$image = static::filter($image, $plan);

			$this->encode($image, $plan, $file);
		} catch (ErrorException|ValueError $exception) {
			throw new ProcessException(
				message: 'GD failed on ' . $plan->source->file . ': ' . $exception->getMessage(),
				previous: $exception
			);
		} finally {
			restore_error_handler();
		}
	}

	protected function quality(Plan $plan): int
	{
		if ($plan->format === Format::Avif) {
			return Quality::libgd($plan->quality);
		}

		$whole =
			$plan->format === Format::Jpeg &&
			$this->cache('whole', static::whole(...)) === true;

		[$quality, $kept] = Quality::for(
			$plan->format,
			$plan->quality,
			$whole
		);

		// libgd would keep the chroma whole from 90 on
		if ($whole === true && $kept === false) {
			return min($quality, 89);
		}

		return $quality;
	}

	protected function reads(Source $source): bool
	{
		// GD returns a GIF's first frame off its screen
		// and AVIF's coded image around its clean aperture
		if ($source->canvas() !== null) {
			return false;
		}

		// few builds decode an animated WebP
		if ($source->format() === Format::Webp) {
			return
				$this->cache('animation', static::decodesAnimation(...)) === true ||
				$source->isAnimated() === false;
		}

		return true;
	}

	protected static function resample(GdImage $image, Plan $plan): GdImage
	{
		$region   = $plan->region();
		$resample = $plan->resample();

		return static::resize($image, $region, $resample);
	}

	protected static function resize(
		GdImage $image,
		Rect $rect,
		Size $size
	): GdImage {
		$resized = static::blank($size->width, $size->height);

		imagecopyresampled(
			$resized,
			$image,
			0,
			0,
			$rect->x,
			$rect->y,
			$size->width,
			$size->height,
			$rect->width,
			$rect->height
		);

		return $resized;
	}

	protected static function sharpen(
		GdImage $image,
		int $amount
	): void {
		imageconvolution(
			image:   $image,
			matrix:  Sharpen::kernel($amount),
			divisor: 1.0,
			offset:  0.5
		);
	}

	protected static function smooth(GdImage $image, int $passes): void
	{
		for ($pass = 0; $pass < $passes; $pass++) {
			imageconvolution(
				image:   $image,
				matrix:  [[1, 2, 1], [2, 4, 2], [1, 2, 1]],
				divisor: 16,
				offset:  0.5
			);

		}
	}

	protected static function threshold(GdImage $image): void
	{
		$width  = imagesx($image);
		$height = imagesy($image);
		$cut    = static::blank($width, $height);

		// at least half transparent becomes fully transparent,
		// which blending leaves as the clear canvas under it
		imagefilter($image, IMG_FILTER_COLORIZE, 0, 0, 0, 63);
		imagefilledrectangle($cut, 0, 0, $width - 1, $height - 1, self::CLEAR);
		imagealphablending($cut, true);
		imagecopy($cut, $image, 0, 0, 0, 0, $width, $height);
		imagecolortransparent($cut, self::CLEAR);

		// merging copies the others without alpha
		// and skips the transparent color
		imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, self::CLEAR);
		imagecopymerge($image, $cut, 0, 0, 0, 0, $width, $height, 100);
	}

	/**
	 * Whether libgd keeps JPEG's chroma whole at quality 90
	 */
	protected static function whole(): bool
	{
		$image = static::blank(8, 8);

		/** @var resource $stream */
		$stream = fopen('php://memory', 'w+b');

		imagejpeg($image, $stream, 90);
		rewind($stream);

		$data  = (string)stream_get_contents($stream);
		$frame = (int)strpos($data, "\xFF\xC0");

		// the first component's sampling factors, after the marker, length,
		// precision, height, width, number of components and its id
		return substr($data, $frame + 11, 1) === "\x11";
	}
}
