<?php

namespace Kirby\Image;

use Exception;

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
	public static $types = [
		'gd' => 'Kirby\Image\Darkroom\GdLib',
		'im' => 'Kirby\Image\Darkroom\ImageMagick'
	];

	/**
	 * @var array
	 */
	protected $settings = [];

	/**
	 * Darkroom constructor
	 *
	 * @param array $settings
	 */
	public function __construct(array $settings = [])
	{
		$this->settings = array_merge($this->defaults(), $settings);
	}

	/**
	 * Creates a new Darkroom instance for the given
	 * type/driver
	 *
	 * @param string $type
	 * @param array $settings
	 * @return mixed
	 * @throws \Exception
	 */
	public static function factory(string $type, array $settings = [])
	{
		if (isset(static::$types[$type]) === false) {
			throw new Exception('Invalid Darkroom type');
		}

		$class = static::$types[$type];
		return new $class($settings);
	}

	/**
	 * Returns the default thumb settings
	 *
	 * @return array
	 */
	protected function defaults(): array
	{
		return [
			'autoOrient'  => true,
			'blur'        => false,
			'crop'        => false,
			'format'      => null,
			'grayscale'   => false,
			'height'      => null,
			'quality'     => 90,
			'scaleHeight' => null,
			'scaleWidth'  => null,
			'width'       => null,
		];
	}

	/**
	 * Normalizes all thumb options
	 *
	 * @param array $options
	 * @return array
	 */
	protected function options(array $options = []): array
	{
		$options = array_merge($this->settings, $options);

		// normalize the crop option
		if ($options['crop'] === true) {
			$options['crop'] = 'center';
		}

		// normalize the blur option
		if ($options['blur'] === true) {
			$options['blur'] = 10;
		}

		// normalize the greyscale option
		if (isset($options['greyscale']) === true) {
			$options['grayscale'] = $options['greyscale'];
			unset($options['greyscale']);
		}

		// normalize the bw option
		if (isset($options['bw']) === true) {
			$options['grayscale'] = $options['bw'];
			unset($options['bw']);
		}

		if ($options['quality'] === null) {
			$options['quality'] = $this->settings['quality'];
		}

		return $options;
	}

	/**
	 * Calculates the dimensions of the final thumb based
	 * on the given options and returns a full array with
	 * all the final options to be used for the image generator
	 *
	 * @param string $file
	 * @param array $options
	 * @return array
	 */
	public function preprocess(string $file, array $options = [])
	{
		$options = $this->options($options);
		$image   = new Image($file);

		$dimensions      = $image->dimensions();
		$thumbDimensions = $dimensions->thumb($options);

		$sourceWidth  = $image->width();
		$sourceHeight = $image->height();

		$options['width']  = $thumbDimensions->width();
		$options['height'] = $thumbDimensions->height();

		// scale ratio compared to the source dimensions
		$options['scaleWidth']  = $sourceWidth ? $options['width'] / $sourceWidth : null;
		$options['scaleHeight'] = $sourceHeight ? $options['height'] / $sourceHeight : null;

		return $options;
	}

	/**
	 * This method must be replaced by the driver to run the
	 * actual image processing job.
	 *
	 * @param string $file
	 * @param array $options
	 * @return array
	 */
	public function process(string $file, array $options = []): array
	{
		return $this->preprocess($file, $options);
	}
}
