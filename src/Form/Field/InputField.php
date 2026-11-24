<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Base class for value fields that can be edited by the user
 *
 * Concrete subclasses must declare a typed `$value` property with a
 * default that defines the empty value.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class InputField extends ValueField
{
	use Mixin\Autofocus;
	use Mixin\Disabled;
	use Mixin\Help;
	use Mixin\Label;
	use Mixin\Required;
	use Mixin\Validation;

	public function props(): array
	{
		return [
			...parent::props(),
			'translate' => $this->translate(),
		];
	}
}
