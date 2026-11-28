<?php

declare(strict_types = 1);

namespace Darkroom;

/**
 * EXIF orientation
 */
enum Orientation: int
{
	case FlipHorizontal = 2;
	case FlipVertical = 4;
	case None = 1;
	case Rotate180 = 3;
	case Rotate270 = 8;
	case Rotate90 = 6;
	case Transpose = 5;
	case Transverse = 7;

	public function apply(Size $size): Size
	{
		if ($this->isSwapped() === true) {
			return new Size($size->height, $size->width);
		}

		return $size;
	}

	public function isFlipped(): bool
	{
		return match ($this) {
			self::FlipHorizontal,
			self::FlipVertical,
			self::Transpose,
			self::Transverse => true,
			default          => false
		};
	}

	public function isSwapped(): bool
	{
		return $this->value >= 5;
	}

	/**
	 * Where `$rect` on the image displayed at `$size`
	 * lies before the orientation is applied
	 */
	public function map(Rect $rect, Size $size): Rect
	{
		$right  = $size->width - $rect->x - $rect->width;
		$bottom = $size->height - $rect->y - $rect->height;

		return match ($this) {
			self::None => $rect,
			self::FlipHorizontal => new Rect(
				x:      $right,
				y:      $rect->y,
				width:  $rect->width,
				height: $rect->height
			),
			self::Rotate180 => new Rect(
				x:      $right,
				y:      $bottom,
				width:  $rect->width,
				height: $rect->height
			),
			self::FlipVertical => new Rect(
				x:      $rect->x,
				y:      $bottom,
				width:  $rect->width,
				height: $rect->height
			),
			self::Transpose => new Rect(
				x:      $rect->y,
				y:      $rect->x,
				width:  $rect->height,
				height: $rect->width
			),
			self::Rotate90 => new Rect(
				x:      $rect->y,
				y:      $right,
				width:  $rect->height,
				height: $rect->width
			),
			self::Transverse => new Rect(
				x:      $bottom,
				y:      $right,
				width:  $rect->height,
				height: $rect->width
			),
			self::Rotate270 => new Rect(
				x:      $bottom,
				y:      $rect->x,
				width:  $rect->height,
				height: $rect->width
			),
		};
	}

	public function turns(): int
	{
		return match ($this) {
			self::None,
			self::FlipHorizontal => 0,
			self::Rotate90,
			self::Transpose      => 1,
			self::Rotate180,
			self::FlipVertical   => 2,
			self::Rotate270,
			self::Transverse     => 3,
		};
	}
}
