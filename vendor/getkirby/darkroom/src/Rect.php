<?php

declare(strict_types = 1);

namespace Darkroom;

final readonly class Rect
{
	public function __construct(
		public int $x,
		public int $y,
		public int $width,
		public int $height
	) {
	}
}
