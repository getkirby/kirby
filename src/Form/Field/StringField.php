<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * String Field
 *
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

	protected string $value = '';

	public function __construct(
		string|null $autocomplete = null,
		string|null $converter = null,
		bool|null $counter = null,
		string|null $font = null,
		int|null $maxlength = null,
		int|null $minlength = null,
		array|string|null $placeholder = null,
		bool|null $spellcheck = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

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
