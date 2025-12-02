<?php

namespace Kirby\Form\Field;

use Kirby\Form\Mixin;

/**
 * Input class for fields that have a value
 *
 * @package   Kirby Field
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
abstract class InputField extends BaseField
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
		string|null $name = null,
		bool|null $required = null,
		bool|null $translate = null,
		array|null $when = null,
		string|null $width = null
	) {
		parent::__construct(
			name: $name,
			when: $when
		);

		$this->autofocus = $autofocus;
		$this->default   = $default;
		$this->disabled  = $disabled;
		$this->help      = $help;
		$this->label     = $label;
		$this->required  = $required;
		$this->translate = $translate;
		$this->width     = $width;
	}

	public function hasValue(): bool
	{
		return true;
	}

	public function props(): array
	{
		return [
			...parent::props(),
			'autofocus' => $this->autofocus(),
			'default'   => $this->default(),
			'disabled'  => $this->isDisabled(),
			'help'      => $this->help(),
			'label'     => $this->label(),
			'translate' => $this->translate(),
			'required'  => $this->isRequired(),
			'width'     => $this->width(),
		];
	}
}
