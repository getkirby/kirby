<?php

namespace Kirby\Image\Darkroom;

use Exception;
use Imagick;
use Kirby\Image\Darkroom;
use Kirby\Image\Focus;

/**
* ImageMagick
*
* @package   Kirby Image
* @author    Nico Hoffmann <nico@getkirby.com>
* @link      https://getkirby.com
* @copyright Bastian Allgeier
* @license   https://opensource.org/licenses/MIT
*/
class ImageMagick extends Darkroom
{
	protected function autoOrient(Imagick $image): void
	{
		switch ($image->getImageOrientation()) {
			case Imagick::ORIENTATION_TOPLEFT:
				break;
			case Imagick::ORIENTATION_TOPRIGHT:
				$image->flopImage();
				break;
			case Imagick::ORIENTATION_BOTTOMRIGHT:
				$image->rotateImage("#000", 180);
				break;
			case Imagick::ORIENTATION_BOTTOMLEFT:
				$image->flopImage();
				$image->rotateImage("#000", 180);
				break;
			case Imagick::ORIENTATION_LEFTTOP:
				$image->flopImage();
				$image->rotateImage("#000", -90);
				break;
			case Imagick::ORIENTATION_RIGHTTOP:
				$image->rotateImage("#000", 90);
				break;
			case Imagick::ORIENTATION_RIGHTBOTTOM:
				$image->flopImage();
				$image->rotateImage("#000", 90);
				break;
			case Imagick::ORIENTATION_LEFTBOTTOM:
				$image->rotateImage("#000", -90);
				break;
			default: // Invalid orientation
				break;
		}

		$image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
	}

	/**
	 * Applies the blur settings
	 */
	protected function blur(Imagick $image, array $options): Imagick
	{
		if ($options['blur'] !== false) {
			return $image->blurImage(0.0, $options['blur']);
		}

		return $image;
	}

	/**
	 * Keep animated gifs
	 */
	protected function coalesce(Imagick $image): Imagick
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
	protected function grayscale(Imagick $image, array $options): void
	{
		if ($options['grayscale'] === true) {
			$image->setColorspace(Imagick::COLORSPACE_GRAY);
		}
	}

	/**
	 * Applies sharpening if activated in the options.
	 */
	protected function sharpen(Imagick $image, array $options): Imagick
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
	protected function interlace(Imagick $image, array $options): void
	{
		if ($options['interlace'] === true) {
			$image->setInterlaceScheme(Imagick::INTERLACE_LINE);
		}
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
		$image   = new Imagick($file);

		$profiles = $image->getImageProfiles('icc', true);

		$this->threads($image, $options);
		$image->stripImage();
		$this->interlace($image, $options);

		$image = $this->coalesce($image);
		$this->grayscale($image, $options);
		$this->autoOrient($image);
		$this->resize($image, $options);
		$this->quality($image, $options);
		$image = $this->blur($image, $options);
		$image = $this->sharpen($image, $options);

		if ($profiles !== []) {
			$image->profileImage('icc', $profiles['icc']);
		}

		if ($this->save($image, $file, $options) === false) {
			throw new Exception(message: 'The imagemagick result could not be generated');
		}

		return $options;
	}

	/**
	 * Applies the correct JPEG compression quality settings
	 */
	protected function quality(Imagick $image, array $options): void
	{
		$image->setImageCompressionQuality($options['quality']);
	}

	/**
	 * Creates the correct options to crop or resize the image
	 * and translates the crop positions for imagemagick
	 */
	protected function resize(Imagick $image, array $options): void
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
			'top left'     => Imagick::GRAVITY_NORTHWEST,
			'top'          => Imagick::GRAVITY_NORTH,
			'top right'    => Imagick::GRAVITY_NORTHEAST,
			'left'         => Imagick::GRAVITY_WEST,
			'right'        => Imagick::GRAVITY_EAST,
			'bottom left'  => Imagick::GRAVITY_SOUTHWEST,
			'bottom'       => Imagick::GRAVITY_SOUTH,
			'bottom right' => Imagick::GRAVITY_SOUTHEAST,
			default        => Imagick::GRAVITY_CENTER
		};

		$image->thumbnailImage($options['width'], $options['height']);
		$image->setGravity($gravity);
		$image->cropImage($options['width'], $options['height'], 0, 0);
	}

	/**
	 * Creates the option for the output file
	 */
	protected function save(Imagick $image, string $file, array $options): bool
	{
		if ($options['format'] !== null) {
			$file = pathinfo($file, PATHINFO_DIRNAME) . '/' . pathinfo($file, PATHINFO_FILENAME) . '.' . $options['format'];
		}

		return $image->writeImage($file);
	}

	/**
	 * Sets thread limit
	 */
	protected function threads(Imagick $image, array $options): void
	{
		$image->setResourceLimit(
			Imagick::RESOURCETYPE_THREAD,
			$options['threads']
		);
	}
}
