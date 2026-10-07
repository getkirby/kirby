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
		$value  = $this->attr($attr);
		$length = (float)$value;

		if ($length <= 0 || Str::endsWith($value, '%') === true) {
			return null;
		}

		return $length;
	}

	/**
	 * Returns the ratio of the width and height
	 */
	public function ratio(): float
	{
		[$width, $height] = $this->size();

		if ($width === null || $height === null || $width <= 0 || $height <= 0) {
			return 0.0;
		}

		return $width / $height;
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
			return [$box[2] ?? null, $box[3] ?? null];
		}

		if ($box !== null && $box[2] > 0 && $box[3] > 0) {
			$width  ??= $height * $box[2] / $box[3];
			$height ??= $width * $box[3] / $box[2];
		}

		return [$width, $height];
	}

	/**
	 * Returns the viewBox as min-x, min-y, width, height
	 */
	public function viewBox(): array|null
	{
		$value = trim($this->attr('viewBox'));
		$box   = array_map(floatval(...), preg_split('/[\s,]+/', $value));

		if (count($box) !== 4 || $box[2] < 0 || $box[3] < 0) {
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
