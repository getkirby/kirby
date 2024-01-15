<?php

namespace Kirby\Block;

use Kirby\Content\Field;

/**
 * Represents a quote block
 * @since 4.1.0
 *
 * @package   Kirby Block
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class QuoteBlock extends TextBlock
{
	public function citation(): Field
	{
		return $this->content()->citation();
	}

	public function controller(): array
	{
		return [
			...parent::controller(),
			'citation' => $this->citation(),
		];
	}
}
