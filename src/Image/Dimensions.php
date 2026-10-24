<?php

namespace Kirby\Image;

use Darkroom\Size;
use Stringable;

/**
 * Width and height of an image as displayed
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
readonly class Dimensions implements Stringable
{
	public function __construct(
		protected int $width,
		protected int $height
	) {
	}

	/**
	 * Improved `var_dump` output
	 * @codeCoverageIgnore
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
	 * From a size of the image library
	 */
	public static function from(Size $size): static
	{
		return new static($size->width, $size->height);
	}

	public function height(): int
	{
		return $this->height;
	}

	public function isLandscape(): bool
	{
		return $this->width > $this->height;
	}

	public function isPortrait(): bool
	{
		return $this->height > $this->width;
	}

	public function isSquare(): bool
	{
		return $this->width === $this->height;
	}

	/**
	 * `landscape`, `portrait` or `square`;
	 * `false` without a readable size
	 */
	public function orientation(): string|false
	{
		return match (true) {
			$this->ratio() === 0.0 => false,
			$this->isPortrait()    => 'portrait',
			$this->isLandscape()   => 'landscape',
			default                => 'square'
		};
	}

	/**
	 * Width divided by height; `0.0` without a readable size
	 */
	public function ratio(): float
	{
		if ($this->width === 0 || $this->height === 0) {
			return 0.0;
		}

		return $this->width / $this->height;
	}

	public function toArray(): array
	{
		return [
			'width'       => $this->width,
			'height'      => $this->height,
			'ratio'       => $this->ratio(),
			'orientation' => $this->orientation(),
		];
	}

	public function width(): int
	{
		return $this->width;
	}
}
