<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Text Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class TextField extends StringField
{
	use Mixin\After;
	use Mixin\Before;
	use Mixin\Converter;
	use Mixin\Icon;
	use Mixin\Pattern;

	public function __construct(
		array|string|null $after = null,
		bool|null $autocomplete = null,
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
			autocomplete: $autocomplete,
			autofocus: $autofocus,
			counter: $counter,
			default: $default,
			disabled: $disabled,
			font: $font,
			help: $help,
			label: $label,
			name: $name,
			maxlength: $maxlength,
			minlength: $minlength,
			placeholder: $placeholder,
			required: $required,
			spellcheck: $spellcheck,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->after     = $after;
		$this->before    = $before;
		$this->converter = $converter;
		$this->icon      = $icon;
		$this->pattern   = $pattern;
	}

	public function default(): string|null
	{
		return $this->convert($this->default);
	}

	public function fill(mixed $value): static
	{
		$this->value = $this->convert($value);
		return $this;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'after'     => $this->after(),
			'before'    => $this->before(),
			'converter' => $this->converter(),
			'icon'      => $this->icon(),
			'pattern'   => $this->pattern()
		];
	}

	protected function validations(): array
	{
		return [
			...parent::validations(),
			'pattern'
		];
	}
}
