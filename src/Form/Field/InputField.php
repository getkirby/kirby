<?php

namespace Kirby\Form\Field;

use Kirby\Form\Fields;
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
	use Mixin\Value;
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

	/**
	 * Creates a new field instance from a $props array
	 * @since 6.0.0
	 */
	public static function factory(
		array $props,
		Fields|null $siblings = null
	): static {
		/**
		 * @var \Kirby\Form\Field\InputField $field
		 */
		$field = parent::factory($props, $siblings);

		if (array_key_exists('value', $props) === true) {
			$field->fill($props['value']);
		}

		return $field;
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

	/**
	 * @since 5.2.0
	 * @todo Move to `Value` mixin once array-based fields are unsupported
	 */
	public function reset(): static
	{
		$this->value = $this->emptyValue();
		return $this;
	}
}
