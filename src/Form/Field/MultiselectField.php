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
	public function accept(): string
	{
		return match($this->accept) {
			'all'   => 'all',
			default => 'options'
		};
	}

	public function icon(): string
	{
		return $this->icon ?? 'checklist';
	}
}
