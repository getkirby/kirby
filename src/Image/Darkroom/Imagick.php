<?php

namespace Kirby\Image\Darkroom;

use Exception;
use Imagick as Image;
use Kirby\Image\Darkroom;
use Kirby\Image\Focus;

/**
 * Imagick
 *
 * @package   Kirby Image
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
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
			return $image->blurImage(0.0, $options['blur']);
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
			'threads'   => 1,
		];
	}

	/**
	 * Applies the correct settings for grayscale images
	 */
	protected function grayscale(Image $image, array $options): Image
	{
		if ($options['grayscale'] === true) {
			$image->setColorspace(Image::COLORSPACE_GRAY);
		}

		return $image;
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
		return $image->sharepenImage(0.0, $amount);
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
			throw new Exception(message: 'The imagemagick result could not be generated');
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
		// simple resize
		if ($options['crop'] === false) {
			$image->thumbnailImage(
				$options['width'],
				$options['height']
			);
		}

		// crop based on focus point
		if (Focus::isFocalPoint($options['crop']) === true) {
			if ($focus = Focus::coords(
				$options['crop'],
				$options['sourceWidth'],
				$options['sourceHeight'],
				$options['width'],
				$options['height']
			)) {
				$image->cropImage(
					$options['width'],
					$options['height'],
					$focus['x1'],
					$focus['y1']
				);

				$image->thumbnailImage(
					$options['width'],
					$options['height']
				);
			}
		}

		// translate the gravity option into something imagemagick understands
		$gravity = match ($options['crop'] ?? null) {
			'top left'     => Image::GRAVITY_NORTHWEST,
			'top'          => Image::GRAVITY_NORTH,
			'top right'    => Image::GRAVITY_NORTHEAST,
			'left'         => Image::GRAVITY_WEST,
			'right'        => Image::GRAVITY_EAST,
			'bottom left'  => Image::GRAVITY_SOUTHWEST,
			'bottom'       => Image::GRAVITY_SOUTH,
			'bottom right' => Image::GRAVITY_SOUTHEAST,
			default        => Image::GRAVITY_CENTER
		};

		$image->thumbnailImage($options['width'], $options['height']);
		$image->setGravity($gravity);
		$image->cropImage($options['width'], $options['height'], 0, 0);

		return $image;
	}

	/**
	 * Creates the option for the output file
	 */
	protected function save(Image $image, string $file, array $options): bool
	{
		if ($options['format'] !== null) {
			$file = pathinfo($file, PATHINFO_DIRNAME) . '/' . pathinfo($file, PATHINFO_FILENAME) . '.' . $options['format'];
		}

		return $image->writeImage($file);
	}

	/**
	 * Removes all metadata but ICC profiles from the image
	 */
	protected function strip(Image $image, array $options): Image
	{
		// get the ICC profile before stripping
		$profiles = $image->getImageProfiles('icc', true);

		// strip all metadata
		$image->stripImage();

		// re-apply the ICC profile, if it exists
		if ($icc = $profiles['icc'] ?? null) {
			// temporarily save in different format for PNG files
			if (strtolower($image->getImageFormat()) === 'png') {
				$blob  = $image->getImageBlob();
				$image = new Image();
				$image->readImageBlob($blob);
			}

			$image->profileImage('icc', $icc);
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
