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
	use Mixin\Maxlength;
	use Mixin\Minlength;
	use Mixin\Placeholder;
	use Mixin\Spellcheck;

	protected string $value = '';

	public function __construct(
		int|null $maxlength = null,
		int|null $minlength = null,
		array|string|null $placeholder = null,
		bool|null $spellcheck = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->maxlength   = $maxlength;
		$this->minlength   = $minlength;
		$this->placeholder = $placeholder;
		$this->spellcheck  = $spellcheck;
	}

	public function props(): array
	{
		return [
			...parent::props(),
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
