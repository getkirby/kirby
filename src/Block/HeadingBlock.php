<?php

namespace Kirby\Block;

use Kirby\Content\Field;

/**
 * Represents a heading block
 * @since 4.1.0
 *
 * @package   Kirby Block
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class HeadingBlock extends TextBlock
{
	public function controller(): array
	{
		return [
			...parent::controller(),
			'level' => $this->level(),
		];
	}

	public function level(): Field
	{
		return $this->content()->level()->or('h2');
	}
}
