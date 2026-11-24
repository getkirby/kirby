<?php

namespace Kirby\Form\Field;

use Kirby\Reflection\Attributes\Prop;

/**
 * Email Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class EmailField extends TextField
{
	#[Prop]
	protected string|null $autocomplete = 'email';

	#[Prop]
	protected bool $counter = false;

	#[Prop]
	protected string|null $icon = 'email';

	#[Prop(computed: true)]
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
