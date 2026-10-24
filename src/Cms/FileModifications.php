<?php

namespace Kirby\Cms;

use Darkroom\Focus;
use Darkroom\Options;
use Kirby\Content\Field;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\Asset;
use Kirby\Toolkit\BlockCollectionAccess;

/**
 * Trait for image resizing, blurring etc.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait FileModifications
{
	/**
	 * Blurs the image by the given amount of pixels
	 */
	#[BlockCollectionAccess]
	public function blur(int|bool $pixels = true): FileVersion|File|Asset
	{
		return $this->thumb(['blur' => $pixels]);
	}

	/**
	 * Converts the image to black and white
	 */
	#[BlockCollectionAccess]
	public function bw(): FileVersion|File|Asset
	{
		return $this->thumb(['grayscale' => true]);
	}

	/**
	 * Crops the image by the given width and height
	 *
	 * @param int|string|Field|array{quality?: int, crop?: bool|string}|null $options Quality, crop position or an array with both
	 */
	#[BlockCollectionAccess]
	public function crop(
		int $width,
		int|null $height = null,
		$options = null
	): FileVersion|File|Asset {
		$options = match (true) {
			is_int($options)          => ['quality' => $options],
			is_string($options)       => ['crop' => $options],
			$options instanceof Field => ['crop' => $options->value()],
			is_array($options)        => $options,
			default                   => []
		};

		return $this->thumb([
			'width'   => $width,
			'height'  => $height,
			'quality' => $options['quality'] ?? null,
			'crop'    => $options['crop'] ?? true
		]);
	}

	/**
	 * Alias for File::bw()
	 */
	#[BlockCollectionAccess]
	public function grayscale(): FileVersion|File|Asset
	{
		return $this->bw();
	}

	/**
	 * Alias for File::bw()
	 */
	#[BlockCollectionAccess]
	public function greyscale(): FileVersion|File|Asset
	{
		return $this->bw();
	}

	/**
	 * Sets the JPEG compression quality
	 */
	#[BlockCollectionAccess]
	public function quality(int $quality): FileVersion|File|Asset
	{
		return $this->thumb(['quality' => $quality]);
	}

	/**
	 * Resizes the file with the given width and height
	 * while keeping the aspect ratio.
	 *
	 * @throws InvalidArgumentException
	 */
	#[BlockCollectionAccess]
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
	 * Sharpens the image, by the library's
	 * default amount for `true`
	 */
	#[BlockCollectionAccess]
	public function sharpen(int|bool $amount = true): FileVersion|File|Asset
	{
		return $this->thumb(['sharpen' => $amount]);
	}

	/**
	 * Create a srcset definition for the given sizes
	 * Sizes can be defined as a simple array. They can
	 * also be set up in the config with the thumbs.srcsets option.
	 * @since 3.1.0
	 *
	 * @param array<array-key, int|string|array<string, mixed>>|string|null $sizes Sizes or the name of a `thumbs.srcsets` preset
	 */
	#[BlockCollectionAccess]
	public function srcset(array|string|null $sizes = null): string|null
	{
		// a set by name, the default one without sizes
		if (is_array($sizes) === false || $sizes === []) {
			$sizes = $this->kirby()->option('thumbs.srcsets.' . ($sizes ?: 'default'));
		}

		if (is_array($sizes) === false || $sizes === []) {
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
	 * @param array<string, mixed>|string|null $options Thumb options or the name of a `thumbs.presets` preset
	 * @throws InvalidArgumentException
	 */
	#[BlockCollectionAccess]
	public function thumb(
		array|string|null $options = null
	): FileVersion|File|Asset {
		// a preset by name, the default one without options
		if (is_array($options) === false || $options === []) {
			$options = $this->kirby()->option('thumbs.presets.' . ($options ?: 'default'));
		}

		if (is_array($options) === false || $options === []) {
			return $this;
		}

		// `crop: true` centres on a valid focus point of the file
		if (($options['crop'] ?? null) === true && $this instanceof File) {
			$options['crop'] = Focus::tryFrom($this->focus()->value()) ?? true;
		}

		// normalised by the library: aliases and `true` resolved,
		// unset ones dropped; invalid options throw in the template
		$options   = Options::from($options)->toArray();
		$component = $this->kirby()->component('file::version');
		$result    = $component($this->kirby(), $this, $options);

		if (
			$result instanceof FileVersion === false &&
			$result instanceof File === false &&
			$result instanceof Asset === false
		) {
			throw new InvalidArgumentException(
				message: 'The file::version component must return a File, FileVersion or Asset object'
			);
		}

		return $result;
	}
}
