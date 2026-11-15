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
use Darkroom\Source;
use Darkroom\Tools\Quality;
use Darkroom\Tools\Sharpen;
use Jcupitt\Vips\Config;
use Jcupitt\Vips\Exception as VipsException;
use Jcupitt\Vips\FFI;
use Jcupitt\Vips\ForeignKeep;
use Jcupitt\Vips\Image;
use Jcupitt\Vips\Introspect;
use Jcupitt\Vips\Target;

final class Vips extends Driver
{
	protected function animates(Format $format): bool
	{
		$pixel  = static::pixel();
		$target = Target::newToMemory();

		// two different frames, as savers merge equal ones
		$strip = $pixel->embed(0, 0, 1, 2, ['extend' => 'black']);

		// JPEG XL keeps frames as an animation only with their delays
		$strip->setType(
			FFI::gtypes('VipsArrayInt'),
			'delay',
			[100, 100]
		);

		try {
			$this->write($strip, $target, $format, ['page_height' => 1]);
			$data  = (string)$target->get('blob');
			$image = Image::newFromBuffer($data);
		} catch (VipsException) {
			return false;
		}

		if ($image->typeOf('delay') === 0) {
			return false;
		}

		/** @var list<int> $delays */
		$delays = $image->get('delay');

		// a loader of animations knows the delays of all frames
		return count($delays) === 2;
	}

	/**
	 * For every thumb, as other instances share libvips' settings
	 */
	protected function configure(): void
	{
		// a cache would serve a replaced source from memory
		Config::cacheSetMax(0);

		$threads = $this->threads();
		Config::concurrencySet($threads);

		// libvips' error buffer keeps what earlier calls left,
		// even successful ones, for the next exception's message
		FFI::vips()->vips_error_clear();
	}

	protected function decodes(Format $format): bool
	{
		try {
			$data    = static::sample($format);
			$image   = Image::newFromBuffer($data);
			$average = $image->avg();
		} catch (VipsException) {
			return false;
		}

		// libvips blacks out what the decoder fails on,
		// at times without an error
		return $average > 0;
	}

	protected function defaults(): array
	{
		return [
			'limit'   => Settings::LIMIT,
			'threads' => null,
		];
	}

	protected function encodes(Format $format): bool
	{
		$pixel  = static::pixel();
		$target = Target::newToMemory();

		try {
			$this->write($pixel, $target, $format, []);
		} catch (VipsException) {
			return false;
		}

		return true;
	}

	protected static function filter(Image $image, Plan $plan): Image
	{
		if ($plan->options->grayscale === true) {
			$image = static::gray($image);
		}

		if ($plan->options->blur !== null) {
			$image = $image->gaussblur($plan->options->blur, ['min_ampl' => 0.05]);
		}

		if ($plan->options->sharpen !== null) {
			$image = static::sharpen($image, $plan->options->sharpen);
		}

		return $image;
	}

	/**
	 * Crops, orients and filters each frame of the stack
	 */
	protected static function frames(Image $image, Plan $plan): Image
	{
		$scale  = $plan->scale();
		$region = $plan->region($scale);
		$frames = [];

		// frames are stacked, each as high as the scale
		for (
			$top = 0;
			$top < $image->height;
			$top += $scale->height
		) {
			$frame = $image->crop($region->x, $top + $region->y, $region->width, $region->height);
			$frame = static::orient($frame, $plan->orientation);
			$frame = static::filter($frame, $plan);

			$frames[] = $frame;
		}

		return Image::arrayjoin($frames, ['across' => 1]);
	}

	protected static function gray(Image $image): Image
	{
		if ($image->bands < 3) {
			return $image;
		}

		$rows   = $image->hasAlpha() === true ? [[...self::LUMA, 0], [0, 0, 0, 1]] : [self::LUMA];
		$matrix = Image::newFromArray($rows);
		$gray   = $image->recomb($matrix);
		$gray   = $gray->cast($image->format);

		// recomb keeps the sRGB tag,
		// with which sharpening makes three bands
		return $gray->copy([
			'interpretation' => $image->format === 'ushort' ? 'grey16' : 'b-w'
		]);
	}

	public function info(): array
	{
		if ($this->isAvailable() === false) {
			return parent::info();
		}

		return [
			'library' => 'libvips',
			'version' => Config::version(),
		];
	}

	/**
	 * CMYK as sRGB from the inks alone, as the other drivers
	 * read it without a profile, where libvips assumes a press
	 */
	protected static function inks(Image $image): Image
	{
		$max   = $image->format === 'ushort' ? 65535 : 255;
		$cmy   = $image->extract_band(0, ['n' => 3]);
		$black = $image->extract_band(3);
		$light = $cmy->invert();
		$white = $black->invert();
		$rgb   = $light->multiply($white);

		// rounded back into the image's format
		$rgb = $rgb->linear(1 / $max, 0.5);
		$rgb = $rgb->cast($image->format);

		return $rgb->copy(['interpretation' => 'srgb']);
	}

	public function isAvailable(): bool
	{
		return $this->cache('available', static function (): bool {
			if (class_exists(Image::class) === false) {
				return false;
			}

			// without a language, libvips' gettext asks macOS
			// for one, which hangs forked PHP-FPM workers
			if (getenv('LANG') === false || getenv('LANG') === '') {
				putenv('LANG=C');
			}

			try {
				Config::version();
			} catch (VipsException) {
				return false;
			}

			return true;
		});
	}

	/**
	 * @throws LimitException If the frames exceed the limit
	 * @throws VipsException If libvips can't read the source
	 */
	protected function load(Plan $plan): Image
	{
		$file   = $plan->source->file;
		$scale  = $plan->scale();
		$header = Image::newFromFile($file);
		$pages  = $this->pages($plan, $header);

		// forced, as the plan has already fitted the size
		$options = [
			'height'    => $scale->height,
			'size'      => 'force',
			'no_rotate' => true,
		];

		if (
			$header->interpretation === 'cmyk' &&
			$header->typeOf('icc-profile-data') === 0
		) {
			$image = Image::newFromFile($file, ['access' => 'sequential']);
			$image = static::inks($image);

			return $image->thumbnail_image($scale->width, $options);
		}

		// only then, as loaders without frames take no `n`
		if ($pages > 1) {
			$file .= '[n=-1]';
		}

		return Image::thumbnail($file, $scale->width, $options);
	}

	/**
	 * @return list<string>
	 * @throws VipsException If there is none
	 */
	protected function options(string $suffix): array
	{
		/** @var list<string> */
		return $this->cache($suffix, static function () use ($suffix): array {
			// a miss isn't an error to report later
			FFI::vips()->vips_error_freeze();

			/** @var string|null $saver */
			$saver = FFI::vips()->vips_foreign_find_save_target($suffix);

			FFI::vips()->vips_error_thaw();

			if ($saver === null) {
				throw new VipsException(
					message: 'No saver writes ' . $suffix . ' into a target'
				);
			}

			$introspect = new Introspect($saver);

			return $introspect->optional_input;
		});
	}

	protected static function orient(
		Image $image,
		Orientation $orientation
	): Image {
		if ($orientation === Orientation::None) {
			return $image;
		}

		$turns = $orientation->turns();

		// turns and flips read out of order, which a
		// source loaded line by line can't serve
		$image = $image->copyMemory();

		if ($turns > 0) {
			$image = $image->rot('d' . $turns * 90);
		}

		if ($orientation->isFlipped() === true) {
			$image = $image->flip('horizontal');
		}

		return $image;
	}

	public function orients(Source $source): bool
	{
		return match ($source->format()) {
			Format::Avif,
			Format::Heic,
			Format::Jxl => true,
			default     => false
		};
	}

	/**
	 * The frames to load
	 *
	 * @throws LimitException If they exceed the limit
	 */
	protected function pages(Plan $plan, Image $header): int
	{
		/** @var int|null $limit */
		$limit  = $this->settings['limit'];
		$timed  = $plan->frames === true ? static::timed($header) : 0;
		$pages  = max(1, $timed);
		$pixels = $header->width * $header->height;

		if ($limit !== null && $pages * $pixels > $limit) {
			throw new LimitException(
				message: 'Frames above the limit of ' . $limit . ' pixels: ' . $plan->source->file
			);
		}

		return $pages;
	}

	protected static function pixel(): Image
	{
		$black = Image::black(1, 1, ['bands' => 3]);
		$white = $black->invert();

		return $white->copy(['interpretation' => 'srgb']);
	}

	protected function plays(Source $source): bool
	{
		$format = $source->format();

		try {
			return $this->cache(
				'play ' . $format->name,
				static function () use ($source): bool {
					$image = Image::newFromFile($source->file);

					return static::timed($image) > 0;
				}
			);
		} catch (VipsException) {
			// a file libvips can't read tells nothing about its format
			return false;
		}
	}

	/**
	 * @throws LimitException Before loading, if the frames exceed the limit
	 * @throws ProcessException If libvips fails
	 */
	public function process(Plan $plan, string $file): void
	{
		$this->configure();

		try {
			$image = $this->load($plan);
			$image = static::frames($image, $plan);

			$this->save($image, $plan, $file);
		} catch (VipsException $exception) {
			throw new ProcessException(
				message: 'libvips failed on ' . $plan->source->file . ': ' . $exception->getMessage(),
				previous: $exception
			);
		}
	}

	protected function reads(Source $source): bool
	{
		// libvips' HEIF loader doesn't know AVIF sequences,
		// ImageMagick's may
		if (
			$source->format() === Format::Avif &&
			Image::findLoad($source->file) === null
		) {
			return false;
		}

		return
			$source->isAnimated() === false ||
			$this->plays($source) === true;
	}

	/**
	 * @throws VipsException If libvips can't write the format
	 */
	protected static function sample(Format $format): string
	{
		$sample = self::SAMPLES[$format->name] ?? null;

		if ($sample !== null) {
			return base64_decode($sample);
		}

		$pixel = static::pixel();

		return $pixel->writeToBuffer('.' . $format->extension());
	}

	/**
	 * @throws VipsException If libvips fails
	 */
	protected function save(
		Image $image,
		Plan $plan,
		string $file
	): void {
		// the pixels are oriented; HEIF savers would
		// write the source's orientation regardless
		if ($image->typeOf('orientation') !== 0) {
			$image->remove('orientation');
		}

		$target            = Target::newToFile($file);
		[$quality, $whole] = Quality::for($plan->format, $plan->quality);

		// savers without alpha flatten onto the background; chroma whole or at half
		// resolution both ways (4:2:0), which libvips would write whole from quality
		// 90 on; JPEG's Huffman tables fitted to the image
		$this->write($image, $target, $plan->format, [
			'Q'               => $quality,
			'background'      => [$image->format === 'ushort' ? 65535 : 255],
			'interlace'       => $plan->interlace,
			'keep'            => ForeignKeep::ICC,
			'optimize_coding' => true,
			'page_height'     => $plan->size()->height,
			'subsample_mode'  => $whole === true ? 'off' : 'on',
		]);
	}

	protected static function sharpen(Image $image, int $amount): Image
	{
		$kernel = Sharpen::kernel($amount);

		$bands = $image->hasAlpha() === true ? $image->bands - 1 : $image->bands;

		// the offset rounds, as casting truncates
		$mask  = Image::newFromArray($kernel, 1.0, 0.5);
		$color = $image->extract_band(0, ['n' => $bands]);
		$sharp = $color->conv($mask, ['precision' => 'float']);
		$sharp = $sharp->cast($image->format);

		if ($bands === $image->bands) {
			return $sharp;
		}

		$alpha = $image->extract_band($bands);

		return $sharp->bandjoin($alpha);
	}

	protected function threads(): int
	{
		if ($this->settings['threads'] !== null) {
			return (int)$this->settings['threads'];
		}

		return $this->cache('threads', static function (): int {
			// php-vips declares no getter,
			// so it is found in the loaded libvips
			$vips = \FFI::cdef('int vips_concurrency_get(void);');

			/** @var int $threads */
			$threads = $vips->vips_concurrency_get();

			return min($threads, 4);
		});
	}

	protected static function timed(Image $image): int
	{
		// loaders that give delays also count the frames
		return $image->typeOf('delay') === 0 ? 0 : (int)$image->get('n-pages');
	}

	/**
	 * @throws VipsException If libvips fails
	 */
	protected function write(
		Image $image,
		Target $target,
		Format $format,
		array $options
	): void {
		$suffix = '.' . $format->extension();
		$known  = $this->options($suffix);

		// `keep` passes unchecked,
		// so a saver that can't strip meta fails
		$options = [
			...array_intersect_key($options, array_flip($known)),
			'keep' => $options['keep'] ?? ForeignKeep::NONE,
		];

		$image->writeToTarget($target, $suffix, $options);
	}
}
