<?php

declare(strict_types = 1);

namespace Darkroom;

use InvalidArgumentException;

final readonly class Focus
{
	public function __construct(
		public float $x,
		public float $y
	) {
	}

	/**
	 * As `x% y%`, which `from()` reads back
	 */
	public function __toString(): string
	{
		$percent = fn (float $fraction): string => (string)round($fraction * 100.0, 1) . '%';

		return $percent($this->x) . ' ' . $percent($this->y);
	}

	/**
	 * From a Gravity, its name or `x% y%`
	 *
	 * @throws InvalidArgumentException For an invalid value
	 */
	public static function from(string|Gravity $value): static
	{
		if (is_string($value) === true) {
			$value = Gravity::tryFrom($value) ?? $value;
		}

		if ($value instanceof Gravity) {
			return $value->focus();
		}

		if (preg_match('/^(-?\d+(?:\.\d+)?)%[\s,]+(-?\d+(?:\.\d+)?)%$/D', $value, $match) !== 1) {
			throw new InvalidArgumentException(
				message: 'Invalid focus: ' . $value
			);
		}

		// rounded, so float noise can't move a crop edge by a pixel
		$fraction = fn (string $percent): float => round(min(max((float)$percent / 100.0, 0.0), 1.0), 3);

		return new static(
			x: $fraction($match[1]),
			y: $fraction($match[2])
		);
	}

	public function place(Size $size, Size $canvas): Rect
	{
		$start = fn (
			float $canvas,
			float $size,
			float $focus
		): int => (int)min(
			max(0.0, round($canvas * $focus - $size / 2.0)),
			$canvas - $size
		);

		return new Rect(
			x:      $start($canvas->width, $size->width, $this->x),
			y:      $start($canvas->height, $size->height, $this->y),
			width:  $size->width,
			height: $size->height
		);
	}

	public static function tryFrom(string|Gravity|null $value): static|null
	{
		if ($value === null) {
			return null;
		}

		try {
			return static::from($value);
		} catch (InvalidArgumentException) {
			return null;
		}
	}
}
