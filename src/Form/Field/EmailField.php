<?php

namespace Kirby\Form\Field;

class EmailField extends TextField
{
	public function __construct(
		protected string $name,
		protected array|string|null $after = null,
		protected string|null $autocomplete = 'email',
		protected bool $autofocus = false,
		protected array|string|null $before = null,
		protected mixed $default = null,
		protected bool $disabled = false,
		protected string|null $font = null,
		protected array|string|null $help = null,
		protected string|null $icon = 'email',
		protected array|string|null $label = null,
		protected int|null $maxlength = null,
		protected int|null $minlength = null,
		protected array|string|null $placeholder = 'email.placeholder',
		protected bool $required = false,
		protected bool $translate = true,
		protected array|null $when = null,
		protected string|null $width = null,
		protected mixed $value = ''
	) {
		$this->fill($value);
	}

	public function type(): string
	{
		return 'email';
	}

	protected function validations(): array
	{
		return [
			'minlength',
			'maxlength',
			'email'
		];
	}
}
