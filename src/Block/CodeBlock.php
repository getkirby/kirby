<?php

namespace Kirby\Block;

use Kirby\Content\Field;

/**
 * Represents a code block
 * @since 4.1.0
 *
 * @package   Kirby Block
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class CodeBlock extends Block
{
	public function code(): Field
	{
		return $this->content()->code();
	}

	public function controller(): array
	{
		return [
			...parent::controller(),
			'code'     => $this->code(),
			'language' => $this->language(),
		];
	}

	public function language(): Field
	{
		return $this->content()->language()->or('text');
	}
}
