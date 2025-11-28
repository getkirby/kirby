<?php

namespace Kirby\Form\Field;

/**
 * Email Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class EmailField extends TextField
{
	public function __construct(
		array|string|null $after = null,
		string|null $autocomplete = null,
		bool|null $autofocus = null,
		array|string|null $before = null,
		string|null $converter = null,
		bool|null $counter = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		string|null $font = null,
		string|null $icon = null,
		array|string|null $label = null,
		int|null $maxlength = null,
		int|null $minlength = null,
		string|null $name = null,
		string|null $pattern = null,
		array|string|null $placeholder = null,
		bool|null $required = null,
		bool|null $spellcheck = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			after: $after,
			autocomplete: $autocomplete,
			autofocus: $autofocus,
			before: $before,
			converter: $converter,
			counter: $counter,
			default: $default,
			disabled: $disabled,
			font: $font,
			help: $help,
			icon: $icon,
			label: $label,
			name: $name,
			maxlength: $maxlength,
			minlength: $minlength,
			pattern: $pattern,
			placeholder: $placeholder,
			required: $required,
			spellcheck: $spellcheck,
			translate: $translate,
			when: $when,
			width: $width
		);
	}

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
		return $this->placeholder ?? $this->i18n('email.placeholder');
	}

	protected function validations(): array
	{
		return [
			...parent::validations(),
			'email'
		];
	}
}
