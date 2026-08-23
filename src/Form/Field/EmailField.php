<?php

namespace Kirby\Form\Field;

/**
 * Email Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class EmailField extends TextField
{
	public function autocomplete(): string
	{
		return $this->autocomplete ?? 'email';
	}

	public function counter(): bool
	{
		return $this->counter ?? false;
	}

	public function icon(): string
	{
		return $this->icon ?? 'email';
	}

	public function placeholder(): string
	{
		return parent::placeholder() ?? $this->i18n('email.placeholder');
	}

	protected function validations(): array
	{
		return [
			...parent::validations(),
			'email'
		];
	}
}
