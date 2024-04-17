<?php

namespace Kirby\Cms;

use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Asset;

/**
 * Trait for image resizing, blurring etc.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait FileModifications
{
	/**
	 * Blurs the image by the given amount of pixels
	 */
	public function blur(int|bool $pixels = true): FileVersion|File|Asset
	{
		return $this->thumb(['blur' => $pixels]);
	}

	/**
	 * Converts the image to black and white
	 */
	public function bw(): FileVersion|File|Asset
	{
		return $this->thumb(['grayscale' => true]);
	}

	/**
	 * Crops the image by the given width and height
	 */
	public function crop(
		int $width,
		int|null $height = null,
		$options = null
	): FileVersion|File|Asset {
		$quality = null;
		$crop    = true;

		if (is_int($options) === true) {
			$quality = $options;
		} elseif (is_string($options)) {
			$crop = $options;
		} elseif ($options instanceof Field) {
			$crop = $options->value();
		} elseif (is_array($options)) {
			$quality = $options['quality'] ?? $quality;
			$crop    = $options['crop']    ?? $crop;
		}

		return $this->thumb([
			'width'   => $width,
			'height'  => $height,
			'quality' => $quality,
			'crop'    => $crop
		]);
	}

	/**
	 * Alias for File::bw()
	 */
	public function grayscale(): FileVersion|File|Asset
	{
		return $this->thumb(['grayscale' => true]);
	}

	/**
	 * Alias for File::bw()
	 */
	public function greyscale(): FileVersion|File|Asset
	{
		return $this->thumb(['grayscale' => true]);
	}

	/**
	 * Sets the JPEG compression quality
	 */
	public function quality(int $quality): FileVersion|File|Asset
	{
		return $this->thumb(['quality' => $quality]);
	}

	/**
	 * Resizes the file with the given width and height
	 * while keeping the aspect ratio.
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function resize(
		int|null $width = null,
		int|null $height = null,
		int|null $quality = null
	): FileVersion|File|Asset {
		return $this->thumb([
			'width'   => $width,
			'height'  => $height,
			'quality' => $quality
		]);
	}

	/**
	 * Sharpens the image
	 */
	public function sharpen(int $amount = 50): FileVersion|File|Asset
	{
		return $this->thumb(['sharpen' => $amount]);
	}

	/**
	 * Create a srcset definition for the given sizes
	 * Sizes can be defined as a simple array. They can
	 * also be set up in the config with the thumbs.srcsets option.
	 * @since 3.1.0
	 */
	public function srcset(array|string|null $sizes = null): string|null
	{
		if (empty($sizes) === true) {
			$sizes = $this->kirby()->option('thumbs.srcsets.default', []);
		}

		if (is_string($sizes) === true) {
			$sizes = $this->kirby()->option('thumbs.srcsets.' . $sizes, []);
		}

		if (is_array($sizes) === false || empty($sizes) === true) {
			return null;
		}

		$set = [];

		foreach ($sizes as $key => $value) {
			if (is_array($value)) {
				$options = $value;
				$condition = $key;
			} elseif (is_string($value) === true) {
				$options = [
					'width' => $key
				];
				$condition = $value;
			} else {
				$options = [
					'width' => $value
				];
				$condition = $value . 'w';
			}

			$set[] = $this->thumb($options)->url() . ' ' . $condition;
		}

		return implode(', ', $set);
	}

	/**
	 * Creates a modified version of images
	 * The media manager takes care of generating
	 * those modified versions and putting them
	 * in the right place. This is normally the
	 * `/media` folder of your installation, but
	 * could potentially also be a CDN or any other
	 * place.
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function thumb(
		array|string|null $options = null
	): FileVersion|File|Asset {
		// thumb presets
		if (empty($options) === true) {
			$options = $this->kirby()->option('thumbs.presets.default');
		} elseif (is_string($options) === true) {
			$options = $this->kirby()->option('thumbs.presets.' . $options);
		}

		if (empty($options) === true || is_array($options) === false) {
			return $this;
		}

		// fallback to content file options
		if (($options['crop'] ?? false) === true) {
			if ($this instanceof ModelWithContent === true) {
				$options['crop'] = $this->focus()->value() ?? 'center';
			} else {
				$options['crop'] = 'center';
			}
		}

		// fallback to global config options
		if (isset($options['format']) === false) {
			if ($format = $this->kirby()->option('thumbs.format')) {
				$options['format'] = $format;
			}
		}

		$component = $this->kirby()->component('file::version');
		$result    = $component($this->kirby(), $this, $options);

		if (
			$result instanceof FileVersion === false &&
			$result instanceof File === false &&
			$result instanceof Asset === false
		) {
			throw new InvalidArgumentException('The file::version component must return a File, FileVersion or Asset object');
		}

		return $result;
	}
}
