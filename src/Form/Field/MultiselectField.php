<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Prop;

/**
 * Multiselect Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class MultiselectField extends TagsField
{
	#[Prop]
	protected string $accept = 'options';

	#[Prop]
	protected string|null $icon = 'checklist';

	public function accept(): string
	{
		return match ($this->accept) {
			'all'   => 'all',
			default => 'options'
		};
	}
}
