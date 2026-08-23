<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Option Field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class OptionField extends InputField
{
	use Mixin\Options;

	protected string $value = '';

	public function __construct(
		array|string|null $options = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->options = $options;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'options' => $this->options(),
		];
	}

	protected function validations(): array
	{
		return [
			'option'
		];
	}
}
