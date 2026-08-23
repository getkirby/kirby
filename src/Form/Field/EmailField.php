<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Derived;

/**
 * Email Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class EmailField extends TextField
{
	protected string|null $autocomplete = 'email';
	protected bool $counter = false;
	protected string|null $icon = 'email';

	#[Derived]
	protected array|string|null $placeholder = null;

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
