<?php

namespace Kirby\Image;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Filesystem\F;
use Kirby\Toolkit\Dom;
use Kirby\Toolkit\Str;

/**
 * Provides information about an SVG
 * based on its markup
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.6.2
 */
class Svg
{
	protected Dom|null $dom = null;

	public function __construct(string $svg)
	{
		if ($svg === '') {
			return;
		}

		try {
			$this->dom = new Dom($svg, 'XML');
		} catch (InvalidArgumentException) {
			// invalid markup provides no information
		}
	}

	/**
	 * Returns an attribute of the root element
	 */
	protected function attr(string $name): string
	{
		return $this->dom?->document()->documentElement?->getAttribute($name) ?? '';
	}

	/**
	 * Creates an instance from an SVG file
	 */
	public static function from(string $root): static
	{
		return new static((string)F::read($root));
	}

	/**
	 * Whether the SVG sets its own absolute width or height
	 */
	public function hasNaturalSize(): bool
	{
		return
			$this->length('width') !== null ||
			$this->length('height') !== null;
	}

	/**
	 * Returns the SVG's height
	 */
	public function height(): float|null
	{
		return $this->size()[1];
	}

	/**
	 * Returns an absolute length attribute
	 */
	protected function length(string $attr): float|null
	{
		$value = $this->attr($attr);

		if (Str::endsWith($value, '%') === true) {
			return null;
		}

		return $this->normalizeFloat((float)$value);
	}

	/**
	 * Returns the value if it can be used as size or ratio:
	 * positive and within the int range (as `Dimensions` casts
	 * sizes to int), which rejects values that over- or underflow
	 */
	protected function normalizeFloat(float|null $value): float|null
	{
		// the positive check also rejects NAN
		if ($value > 0 && $value < PHP_INT_MAX) {
			return $value;
		}

		return null;
	}

	/**
	 * Returns the ratio of the width and height
	 */
	public function ratio(): float
	{
		[$width, $height] = $this->size();

		if ($width === null || $height === null) {
			return 0.0;
		}

		return $this->normalizeFloat($width / $height) ?? 0.0;
	}

	/**
	 * Derives width and height like browsers: from the own size,
	 * a missing side from the viewBox ratio, else from the viewBox
	 */
	protected function size(): array
	{
		$width  = $this->length('width');
		$height = $this->length('height');
		$box    = $this->viewBox();

		if ($width === null && $height === null) {
			$width  = $box[2] ?? null;
			$height = $box[3] ?? null;
		} elseif ($box !== null && $box[2] > 0 && $box[3] > 0) {
			// divide first, so that only a result that is
			// too large can overflow, not an intermediate product
			$width  ??= $height * ($box[2] / $box[3]);
			$height ??= $width  * ($box[3] / $box[2]);
		}

		return [$this->normalizeFloat($width), $this->normalizeFloat($height)];
	}

	/**
	 * Returns the viewBox as min-x, min-y, width, height
	 */
	public function viewBox(): array|null
	{
		$value = trim($this->attr('viewBox'));
		$box   = array_map(floatval(...), preg_split('/[\s,]+/', $value));

		// `1e400` overflows to INF
		if (
			count($box) !== 4 ||
			$box[2] < 0 ||
			$box[3] < 0 ||
			array_filter($box, is_finite(...)) !== $box
		) {
			return null;
		}

		return $box;
	}

	/**
	 * Returns the SVG's width
	 */
	public function width(): float|null
	{
		return $this->size()[0];
	}
}
