<?php

namespace Kirby\Image;

/**
 * The Dimension class is used to provide additional
 * methods for images and possibly other objects with
 * width and height to recalculate the size,
 * get the ratio or just the width and height.
 *
 * @package   Kirby Image
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Dimensions
{
	public function __construct(
		public int $width,
		public int $height
	) {
	}

	/**
	 * Improved `var_dump` output
	 */
	public function __debugInfo(): array
	{
		return $this->toArray();
	}

	/**
	 * Echos the dimensions as width × height
	 */
	public function __toString(): string
	{
		return $this->width . ' × ' . $this->height;
	}

	/**
	 * Crops the dimensions by width and height
	 *
	 * @return $this
	 */
	public function crop(int $width, int|null $height = null): static
	{
		$this->width  = $width;
		$this->height = $width;

		if ($height !== 0 && $height !== null) {
			$this->height = $height;
		}

		return $this;
	}

	/**
	 * Returns the height
	 */
	public function height(): int
	{
		return $this->height;
	}

	/**
	 * Recalculates the width and height to fit into the given box.
	 *
	 * <code>
	 *
	 * $dimensions = new Dimensions(1200, 768);
	 * $dimensions->fit(500);
	 *
	 * echo $dimensions->width();
	 * // output: 500
	 *
	 * echo $dimensions->height();
	 * // output: 320
	 *
	 * </code>
	 *
	 * @param int $box the max width and/or height
	 * @param bool $force If true, the dimensions will be
	 *                    upscaled to fit the box if smaller
	 * @return $this object with recalculated dimensions
	 */
	public function fit(int $box, bool $force = false): static
	{
		if ($this->width === 0 || $this->height === 0) {
			$this->width  = $box;
			$this->height = $box;
			return $this;
		}

		$ratio = $this->ratio();

		if ($this->width > $this->height) {
			// wider than tall
			if ($this->width > $box || $force === true) {
				$this->width = $box;
			}
			$this->height = (int)round($this->width / $ratio);
		} elseif ($this->height > $this->width) {
			// taller than wide
			if ($this->height > $box || $force === true) {
				$this->height = $box;
			}
			$this->width = (int)round($this->height * $ratio);
		} elseif ($this->width > $box) {
			// width = height but bigger than box
			$this->width  = $box;
			$this->height = $box;
		}

		return $this;
	}

	/**
	 * Recalculates the width and height to fit the given height
	 *
	 * <code>
	 *
	 * $dimensions = new Dimensions(1200, 768);
	 * $dimensions->fitHeight(500);
	 *
	 * echo $dimensions->width();
	 * // output: 781
	 *
	 * echo $dimensions->height();
	 * // output: 500
	 *
	 * </code>
	 *
	 * @param int|null $fit the max height
	 * @param bool $force If true, the dimensions will be
	 *                    upscaled to fit the box if smaller
	 * @return $this object with recalculated dimensions
	 */
	public function fitHeight(int|null $fit = null, bool $force = false): static
	{
		return $this->fitSize('height', $fit, $force);
	}

	/**
	 * Helper for fitWidth and fitHeight methods
	 *
	 * @param string $ref reference (width or height)
	 * @param int|null $fit the max width
	 * @param bool $force If true, the dimensions will be
	 *                    upscaled to fit the box if smaller
	 * @return $this object with recalculated dimensions
	 */
	protected function fitSize(string $ref, int|null $fit = null, bool $force = false): static
	{
		if ($fit === 0 || $fit === null) {
			return $this;
		}

		if ($this->$ref <= $fit && !$force) {
			return $this;
		}

		$ratio        = $this->ratio();
		$mode         = $ref === 'width';
		$this->width  =  $mode ? $fit : (int)round($fit * $ratio);
		$this->height = !$mode ? $fit : (int)round($fit / $ratio);

		return $this;
	}

	/**
	 * Recalculates the width and height to fit the given width
	 *
	 * <code>
	 *
	 * $dimensions = new Dimensions(1200, 768);
	 * $dimensions->fitWidth(500);
	 *
	 * echo $dimensions->width();
	 * // output: 500
	 *
	 * echo $dimensions->height();
	 * // output: 320
	 *
	 * </code>
	 *
	 * @param int|null $fit the max width
	 * @param bool $force If true, the dimensions will be
	 *                    upscaled to fit the box if smaller
	 * @return $this object with recalculated dimensions
	 */
	public function fitWidth(int|null $fit = null, bool $force = false): static
	{
		return $this->fitSize('width', $fit, $force);
	}

	/**
	 * Recalculates the dimensions by the width and height
	 *
	 * @param int|null $width the max height
	 * @param int|null $height the max width
	 * @return $this
	 */
	public function fitWidthAndHeight(
		int|null $width = null,
		int|null $height = null,
		bool $force = false
	): static {
		if ($this->width > $this->height) {
			$this->fitWidth($width, $force);

			// do another check for the max height
			if ($this->height > $height) {
				$this->fitHeight($height);
			}
		} else {
			$this->fitHeight($height, $force);

			// do another check for the max width
			if ($this->width > $width) {
				$this->fitWidth($width);
			}
		}

		return $this;
	}

	/**
	 * Detect the dimensions for an image file
	 */
	public static function forImage(string $root): static
	{
		if (file_exists($root) === false) {
			return new static(0, 0);
		}

		$size = getimagesize($root);
		return new static($size[0] ?? 0, $size[1] ?? 1);
	}

	/**
	 * Detect the dimensions for a svg file
	 */
	public static function forSvg(string $root): static
	{
		// avoid xml errors
		libxml_use_internal_errors(true);

		$content = file_get_contents($root);
		$height  = 0;
		$width   = 0;
		$xml     = simplexml_load_string($content);

		if ($xml !== false) {
			$attr   = $xml->attributes();
			$width  = (int)($attr->width);
			$height = (int)($attr->height);
			if (($width === 0 || $height === 0) && empty($attr->viewBox) === false) {
				$box    = explode(' ', $attr->viewBox);
				$width  = (int)($box[2] ?? 0);
				$height = (int)($box[3] ?? 0);
			}
		}

		return new static($width, $height);
	}

	/**
	 * Checks if the dimensions are landscape
	 */
	public function landscape(): bool
	{
		return $this->width > $this->height;
	}

	/**
	 * Returns a string representation of the orientation
	 */
	public function orientation(): string|false
	{
		if (!$this->ratio()) {
			return false;
		}

		if ($this->portrait()) {
			return 'portrait';
		}

		if ($this->landscape()) {
			return 'landscape';
		}

		return 'square';
	}

	/**
	 * Checks if the dimensions are portrait
	 */
	public function portrait(): bool
	{
		return $this->height > $this->width;
	}

	/**
	 * Calculates and returns the ratio
	 *
	 * <code>
	 *
	 * $dimensions = new Dimensions(1200, 768);
	 * echo $dimensions->ratio();
	 * // output: 1.5625
	 *
	 * </code>
	 */
	public function ratio(): float
	{
		if ($this->width !== 0 && $this->height !== 0) {
			return $this->width / $this->height;
		}

		return 0;
	}

	/**
	 * Resizes image
	 * @return $this
	 */
	public function resize(
		int|null $width = null,
		int|null $height = null,
		bool $force = false
	): static {
		return $this->fitWidthAndHeight($width, $height, $force);
	}

	/**
	 * Checks if the dimensions are square
	 */
	public function square(): bool
	{
		return $this->width === $this->height;
	}

	/**
	 * Resize and crop
	 *
	 * @return $this
	 */
	public function thumb(array $options = []): static
	{
		$width  = $options['width']  ?? null;
		$height = $options['height'] ?? null;
		$crop   = $options['crop']   ?? false;
		$method = $crop !== false ? 'crop' : 'resize';

		if ($width === null && $height === null) {
			return $this;
		}

		return $this->$method($width, $height);
	}

	/**
	 * Converts the dimensions object
	 * to a plain PHP array
	 */
	public function toArray(): array
	{
		return [
			'width'       => $this->width(),
			'height'      => $this->height(),
			'ratio'       => $this->ratio(),
			'orientation' => $this->orientation(),
		];
	}

	/**
	 * Returns the width
	 */
	public function width(): int
	{
		return $this->width;
	}
}
