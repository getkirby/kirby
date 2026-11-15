<?php

declare(strict_types = 1);

namespace Darkroom;

final readonly class Size
{
	public function __construct(
		public int $width,
		public int $height
	) {
	}

	/**
	 * The largest part with the ratio of `$ratio`
	 */
	public function crop(Size $ratio): static
	{
		// multiplied before dividing, to stay exact
		return match ($ratio->width * $this->height <=> $ratio->height * $this->width) {
			1 => new static(
				$this->width,
				max(1, (int)floor($this->width * $ratio->height / $ratio->width))
			),
			-1 => new static(
				max(1, (int)floor($this->height * $ratio->width / $ratio->height)),
				$this->height
			),
			0 => $this
		};
	}

	/**
	 * Shrinks into the box, keeping the ratio
	 */
	public function fit(
		int|null $width = null,
		int|null $height = null
	): static {
		$width  = $width > 0 ? min($width, $this->width) : $this->width;
		$height = $height > 0 ? min($height, $this->height) : $this->height;

		if ($this->width === 0 || $this->height === 0) {
			return new static($width, $height);
		}

		// either side may set the other: the larger size in the box
		// wins, so that a fitted size fits into itself unchanged
		$wide = new static(
			$width,
			max(1, (int)round($this->height * $width / $this->width))
		);
		$tall = new static(
			max(1, (int)round($this->width * $height / $this->height)),
			$height
		);

		if ($tall->width > $width) {
			return $wide;
		}

		if ($wide->height > $height) {
			return $tall;
		}

		return $tall->pixels() > $wide->pixels() ? $tall : $wide;
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

	public function pixels(): int
	{
		if (
			$this->height !== 0 &&
			$this->width > intdiv(PHP_INT_MAX, $this->height)
		) {
			return PHP_INT_MAX;
		}

		return $this->width * $this->height;
	}

	public function ratio(): float
	{
		if ($this->width === 0 || $this->height === 0) {
			return 0.0;
		}

		return $this->width / $this->height;
	}
}
