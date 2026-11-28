<?php

declare(strict_types = 1);

namespace Darkroom\Formats;

use Darkroom\Format;
use Darkroom\Orientation;
use Darkroom\Size;

/**
 * Size as stored, orientation and format from a reader
 *
 * @internal
 */
final readonly class Header
{
	public function __construct(
		public Size $size = new Size(0, 0),
		public Orientation $orientation = Orientation::None,
		public Format|null $format = null
	) {
	}
}
