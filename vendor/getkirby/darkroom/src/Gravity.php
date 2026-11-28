<?php

declare(strict_types = 1);

namespace Darkroom;

enum Gravity: string
{
	case Bottom = 'bottom';
	case BottomLeft = 'bottom left';
	case BottomRight = 'bottom right';
	case Center = 'center';
	case Left = 'left';
	case Right = 'right';
	case Top = 'top';
	case TopLeft = 'top left';
	case TopRight = 'top right';

	public function focus(): Focus
	{
		return match ($this) {
			self::Bottom      => new Focus(0.5, 1.0),
			self::BottomLeft  => new Focus(0.0, 1.0),
			self::BottomRight => new Focus(1.0, 1.0),
			self::Center      => new Focus(0.5, 0.5),
			self::Left        => new Focus(0.0, 0.5),
			self::Right       => new Focus(1.0, 0.5),
			self::Top         => new Focus(0.5, 0.0),
			self::TopLeft     => new Focus(0.0, 0.0),
			self::TopRight    => new Focus(1.0, 0.0)
		};
	}
}
