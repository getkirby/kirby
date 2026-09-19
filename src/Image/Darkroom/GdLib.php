<?php

namespace Kirby\Image\Darkroom;

use claviska\SimpleImage;
use Kirby\Filesystem\Mime;
use Kirby\Image\Darkroom;
use Kirby\Image\Focus;

/**
 * GdLib darkroom driver
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class GdLib extends Darkroom
{
	/**
	 * Processes the image with the SimpleImage library
	 */
	public function process(string $file, array $options = []): array
	{
		$options = $this->preprocess($file, $options);
		$mime    = $this->mime($options);

		$image = new SimpleImage();
		$image->fromFile($file);
		$image->autoOrient();

		$image = $this->resize($image, $options);
		$image = $this->blur($image, $options);
		$image = $this->grayscale($image, $options);
		$image = $this->sharpen($image, $options);

		$image->toFile($file, $mime, $options);

		return $options;
	}

	/**
	 * Wrapper around SimpleImage's resize and crop methods
	 */
	protected function resize(SimpleImage $image, array $options): SimpleImage
	{
		if ($crop = $options['crop'] ?? null) {
			$image = $this->cover($image, $options);

			if ($focus = Focus::coords(
				$crop,
				$image->getWidth(),
				$image->getHeight(),
				$options['width'],
				$options['height']
			)) {
				$image->crop(
					$focus['x1'],
					$focus['y1'],
					$focus['x2'],
					$focus['y2']
				);
			}

			return $image->thumbnail($options['width'], $options['height']);
		}

		return $image->resize($options['width'], $options['height']);
	}

	/**
	 * Downscales the image to cover the target dimensions
	 * before cropping to avoid a full-resolution copy
	 */
	protected function cover(SimpleImage $image, array $options): SimpleImage
	{
		$ratioSource = Focus::ratio($image->getWidth(), $image->getHeight());
		$ratioThumb  = Focus::ratio($options['width'], $options['height']);

		if ($ratioThumb > $ratioSource) {
			return $image->resize($options['width']);
		}

		return $image->resize(null, $options['height']);
	}

	/**
	 * Applies the correct blur settings for SimpleImage
	 */
	protected function blur(SimpleImage $image, array $options): SimpleImage
	{
		if ($options['blur'] === false) {
			return $image;
		}

		return $image->blur('gaussian', (int)$options['blur']);
	}

	/**
	 * Applies grayscale conversion if activated in the options.
	 */
	protected function grayscale(SimpleImage $image, array $options): SimpleImage
	{
		if ($options['grayscale'] === false) {
			return $image;
		}

		return $image->desaturate();
	}

	/**
	 * Applies sharpening if activated in the options.
	 */
	protected function sharpen(SimpleImage $image, array $options): SimpleImage
	{
		if (is_int($options['sharpen']) === false) {
			return $image;
		}

		return $image->sharpen($options['sharpen']);
	}

	/**
	 * Returns mime type based on `format` option
	 */
	protected function mime(array $options): string|null
	{
		if ($options['format'] === null) {
			return null;
		}

		return Mime::fromExtension($options['format']);
	}
}
