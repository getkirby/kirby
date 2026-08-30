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
	use Mixin\Width;

	public function __construct(
		bool|null $autofocus = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		array|string|null $label = null,
		bool|null $required = null,
		bool|null $translate = null,
		mixed ...$args
	) {
		parent::__construct(...$args);

		$this->autofocus = $autofocus;
		$this->default   = $default;
		$this->disabled  = $disabled;
		$this->help      = $help;
		$this->label     = $label;
		$this->required  = $required;
		$this->translate = $translate;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'autofocus' => $this->autofocus(),
			'disabled'  => $this->isDisabled(),
			'help'      => $this->help(),
			'label'     => $this->label(),
			'translate' => $this->translate(),
			'required'  => $this->isRequired(),
			'width'     => $this->width(),
		];
	}
}
