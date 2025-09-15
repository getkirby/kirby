<?php

namespace Kirby\Image;

use Exception;
use Kirby\Image\Darkroom\GdLib;
use Kirby\Image\Darkroom\ImageMagick;
use Kirby\Image\Darkroom\Imagick;

/**
 * A wrapper around resizing and cropping
 * via GDLib, ImageMagick or other libraries.
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Darkroom
{
	public static array $types = [
		'gd'      => GdLib::class,
		'imagick' => Imagick::class,
		'im'      => ImageMagick::class
	];

	public function __construct(
		protected array $settings = []
	) {
		$this->settings = [...$this->defaults(), ...$settings];
	}

	/**
	 * Creates a new Darkroom instance
	 * for the given type/driver
	 *
	 * @throws \Exception
	 */
	public static function factory(string $type, array $settings = []): static
	{
		if (isset(static::$types[$type]) === false) {
			throw new Exception(message: 'Invalid Darkroom type');
		}

		return new static::$types[$type]($settings);
	}

	/**
	 * Returns the default thumb settings
	 */
	protected function defaults(): array
	{
		return [
			'blur'        => false,
			'crop'        => false,
			'format'      => null,
			'grayscale'   => false,
			'height'      => null,
			'quality'     => 90,
			'scaleHeight' => null,
			'scaleWidth'  => null,
			'sharpen'     => null,
			'width'       => null,
		];
	}

	/**
	 * Normalizes all thumb options
	 */
	protected function options(array $options = []): array
	{
		$options = [
			...$this->settings,
			...$options,
			// ensure quality isn't unset by provided options
			'quality' => $options['quality'] ?? $this->settings['quality']
		];

		// normalize the crop option
		if ($options['crop'] === true) {
			$options['crop'] = 'center';
		}

		// normalize the blur option
		if ($options['blur'] === true) {
			$options['blur'] = 10;
		}

		// normalize the grayscale option
		if (isset($options['greyscale']) === true) {
			$options['grayscale'] = $options['greyscale'];
			unset($options['greyscale']);
		}

		// normalize the bw option
		if (isset($options['bw']) === true) {
			$options['grayscale'] = $options['bw'];
			unset($options['bw']);
		}

		// normalize the sharpen option
		if ($options['sharpen'] === true) {
			$options['sharpen'] = 50;
		}

		return $options;
	}

	/**
	 * Calculates the dimensions of the final thumb based
	 * on the given options and returns a full array with
	 * all the final options to be used for the image generator
	 */
	public function preprocess(string $file, array $options = []): array
	{
		$options = $this->options($options);
		$image   = new Image($file);

		$options['sourceWidth']  = $image->width();
		$options['sourceHeight'] = $image->height();

		$dimensions        = $image->dimensions();
		$thumbDimensions   = $dimensions->thumb($options);

		$options['width']  = $thumbDimensions->width();
		$options['height'] = $thumbDimensions->height();

		// scale ratio compared to the source dimensions
		$options['scaleWidth'] = Focus::ratio(
			$options['width'],
			$options['sourceWidth']
		);
		$options['scaleHeight'] = Focus::ratio(
			$options['height'],
			$options['sourceHeight']
		);

		return $options;
	}

	/**
	 * This method must be replaced by the driver to run the
	 * actual image processing job.
	 */
	public function process(string $file, array $options = []): array
	{
		return $this->preprocess($file, $options);
	}
}
