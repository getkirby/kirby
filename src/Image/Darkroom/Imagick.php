<?php

namespace Kirby\Image\Darkroom;

use Exception;
use Imagick as Image;
use Kirby\Image\Darkroom;
use Kirby\Image\Focus;

/**
 * Imagick darkroom driver
 *
 * @package   Kirby Image
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 */
class Imagick extends Darkroom
{
	protected function autoOrient(Image $image): Image
	{
		switch ($image->getImageOrientation()) {
			case Image::ORIENTATION_TOPLEFT:
				break;
			case Image::ORIENTATION_TOPRIGHT:
				$image->flopImage();
				break;
			case Image::ORIENTATION_BOTTOMRIGHT:
				$image->rotateImage('#000', 180);
				break;
			case Image::ORIENTATION_BOTTOMLEFT:
				$image->flopImage();
				$image->rotateImage('#000', 180);
				break;
			case Image::ORIENTATION_LEFTTOP:
				$image->flopImage();
				$image->rotateImage('#000', -90);
				break;
			case Image::ORIENTATION_RIGHTTOP:
				$image->rotateImage('#000', 90);
				break;
			case Image::ORIENTATION_RIGHTBOTTOM:
				$image->flopImage();
				$image->rotateImage('#000', 90);
				break;
			case Image::ORIENTATION_LEFTBOTTOM:
				$image->rotateImage('#000', -90);
				break;
			default: // Invalid orientation
				break;
		}

		$image->setImageOrientation(Image::ORIENTATION_TOPLEFT);
		return $image;
	}

	/**
	 * Applies the blur settings
	 */
	protected function blur(Image $image, array $options): Image
	{
		if ($options['blur'] !== false) {
			$image->blurImage(0.0, $options['blur']);
		}

		return $image;
	}

	/**
	 * Keep animated gifs
	 */
	protected function coalesce(Image $image): Image
	{
		if ($image->getImageMimeType() === 'image/gif') {
			return $image->coalesceImages();
		}

		return $image;
	}

	/**
	 * Returns additional default parameters for imagemagick
	 */
	protected function defaults(): array
	{
		return parent::defaults() + [
			'interlace' => false,
			'profiles'  => ['icc', 'icm'],
			'threads'   => 1,
		];
	}

	/**
	 * Applies the correct settings for grayscale images
	 */
	protected function grayscale(Image $image, array $options): Image
	{
		if ($options['grayscale'] === true) {
			$image->setImageColorspace(Image::COLORSPACE_GRAY);
		}

		return $image;
	}

	/**
	 * Applies the correct settings for interlaced JPEGs if
	 * activated via options
	 */
	protected function interlace(Image $image, array $options): Image
	{
		if ($options['interlace'] === true) {
			$image->setInterlaceScheme(Image::INTERLACE_LINE);
		}

		return $image;
	}

	/**
	 * Creates and runs the full imagemagick command
	 * to process the image
	 *
	 * @throws \Exception
	 */
	public function process(string $file, array $options = []): array
	{
		$options = $this->preprocess($file, $options);

		$image = new Image($file);
		$image = $this->threads($image, $options);
		$image = $this->interlace($image, $options);
		$image = $this->coalesce($image);
		$image = $this->grayscale($image, $options);
		$image = $this->autoOrient($image);
		$image = $this->resize($image, $options);
		$image = $this->quality($image, $options);
		$image = $this->blur($image, $options);
		$image = $this->sharpen($image, $options);
		$image = $this->strip($image, $options);

		if ($this->save($image, $file, $options) === false) {
			// @codeCoverageIgnoreStart
			throw new Exception(message: 'The imagemagick result could not be generated');
			// @codeCoverageIgnoreEnd
		}

		return $options;
	}

	/**
	 * Applies the correct JPEG compression quality settings
	 */
	protected function quality(Image $image, array $options): Image
	{
		$image->setImageCompressionQuality($options['quality']);
		return $image;
	}

	/**
	 * Creates the correct options to crop or resize the image
	 * and translates the crop positions for imagemagick
	 */
	protected function resize(Image $image, array $options): Image
	{
		if ($crop = $options['crop'] ?? null) {
			if ($focus = Focus::coords(
				$crop,
				$options['sourceWidth'],
				$options['sourceHeight'],
				$options['width'],
				$options['height']
			)) {
				$image->cropImage(
					$focus['width'],
					$focus['height'],
					$focus['x1'],
					$focus['y1']
				);

			}
		}

		$image->thumbnailImage(
			$options['width'],
			$options['height'],
			true
		);

		return $image;
	}

	/**
	 * Creates the option for the output file
	 */
	protected function save(Image $image, string $file, array $options): bool
	{
		// set the output format explicitly if specified;
		// writing to the original path
		if ($options['format'] !== null) {
			$image->setImageFormat($options['format']);
		}

		return $image->writeImages($file, true);
	}

	/**
	 * Applies sharpening if activated in the options.
	 */
	protected function sharpen(Image $image, array $options): Image
	{
		if (is_int($options['sharpen']) === false) {
			return $image;
		}

		$amount = max(1, min(100, $options['sharpen'])) / 100;
		$image->sharpenImage(0.0, $amount);

		return $image;
	}

	/**
	 * Removes all metadata but ICC profiles from the image
	 */
	protected function strip(Image $image, array $options): Image
	{
		// strip all profiles but the ICC profile
		$profiles = $image->getImageProfiles('*', false);

		foreach ($profiles as $profile) {
			if (in_array($profile, $options['profiles'] ?? [], true) === false) {
				$image->removeImageProfile($profile);
			}
		}

		// strip all properties
		$properties = $image->getImageProperties('*', false);

		foreach ($properties as $property) {
			$image->deleteImageProperty($property);
		}

		return $image;
	}

	/**
	 * Sets thread limit
	 */
	protected function threads(Image $image, array $options): Image
	{
		$image->setResourceLimit(
			Image::RESOURCETYPE_THREAD,
			$options['threads']
		);
		return $image;
	}
}
