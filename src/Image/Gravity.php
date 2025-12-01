<?php

namespace Kirby\Image;

/**
 * @package   Kirby Image
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     5.2.0
 */
enum Gravity: string
{
	case TOP = 'top';
	case TOP_LEFT = 'top left';
	case TOP_RIGHT = 'top right';

	case LEFT = 'left';
	case CENTER = 'center';
	case RIGHT = 'right';

	case BOTTOM = 'bottom';
	case BOTTOM_LEFT = 'bottom left';
	case BOTTOM_RIGHT = 'bottom right';

	public function toPercentageString(): string
	{
		return match ($this) {
			static::TOP          => '50% 0%',
			static::TOP_LEFT     => '0% 0%',
			static::TOP_RIGHT    => '100% 0%',
			static::LEFT         => '0% 50%',
			static::CENTER       => '50% 50%',
			static::RIGHT        => '100% 50%',
			static::BOTTOM       => '50% 100%',
			static::BOTTOM_LEFT  => '0% 100%',
			static::BOTTOM_RIGHT => '100% 100%',
		};
	}
}
