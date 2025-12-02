<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * String Field
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class StringField extends InputField
{
	use Mixin\Autocomplete;
	use Mixin\Converter;
	use Mixin\Counter;
	use Mixin\Font;
	use Mixin\Maxlength;
	use Mixin\Minlength;
	use Mixin\Placeholder;
	use Mixin\Spellcheck;

	protected mixed $value = '';

	public function __construct(
		string|null $autocomplete = null,
		bool|null $autofocus = null,
		string|null $converter = null,
		bool|null $counter = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		string|null $font = null,
		array|string|null $label = null,
		int|null $maxlength = null,
		int|null $minlength = null,
		string|null $name = null,
		array|string|null $placeholder = null,
		bool|null $required = null,
		bool|null $spellcheck = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			autofocus: $autofocus,
			default: $default,
			disabled: $disabled,
			help: $help,
			label: $label,
			name: $name,
			required: $required,
			translate: $translate,
			when: $when,
			width: $width
		);

		$this->autocomplete = $autocomplete;
		$this->converter    = $converter;
		$this->counter      = $counter;
		$this->font         = $font;
		$this->maxlength    = $maxlength;
		$this->minlength    = $minlength;
		$this->placeholder  = $placeholder;
		$this->spellcheck   = $spellcheck;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'autocomplete' => $this->autocomplete(),
			'counter'      => $this->counter(),
			'font'         => $this->font(),
			'maxlength'    => $this->maxlength(),
			'minlength'    => $this->minlength(),
			'placeholder'  => $this->placeholder(),
			'spellcheck'   => $this->spellcheck(),
		];
	}

	protected function validations(): array
	{
		return [
			'minlength',
			'maxlength'
		];
	}
}
