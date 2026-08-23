<?php

namespace Kirby\Form\Field;

/**
 * Multiselect Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class MultiselectField extends TagsField
{
	protected string $accept = 'options';

	protected string|null $icon = 'checklist';

	public function accept(): string
	{
		return match ($this->accept) {
			'all'   => 'all',
			default => 'options'
		};
	}
}
