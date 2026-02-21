<?php

namespace Kirby\Form;

use Kirby\Exception\NotFoundException;
use Kirby\Form\Field\InputField;

/**
 * Abstract field class to be used instead
 * of functional field components for more
 * control.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @use \Kirby\Cms\HasSiblings<\Kirby\Form\Fields>
 */
abstract class FieldClass extends InputField
{
	use Mixin\After;
	use Mixin\Before;
	use Mixin\Icon;
	use Mixin\Placeholder;
	use Mixin\Width;

	protected mixed $value = null;

	public function __construct(
		array|string|null $after = null,
		bool|null $autofocus = null,
		array|string|null $before = null,
		mixed $default = null,
		bool|null $disabled = null,
		array|string|null $help = null,
		string|null $icon = null,
		array|string|null $label = null,
		string|null $name = null,
		array|string|null $placeholder = null,
		bool|null $required = null,
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

		$this->after       = $after;
		$this->before      = $before;
		$this->icon        = $icon;
		$this->placeholder = $placeholder;
	}

	public function __call(string $param, array $args): mixed
	{
		if (property_exists($this, $param) === true) {
			return $this->$param;
		}

		throw new NotFoundException(message: 'Method or option "' . $param . '" does not exist for field type "' . $this->type() . '"');
	}

	/**
	 * Define the props that will be sent to
	 * the Vue component
	 */
	public function props(): array
	{
		return [
			'after'       => $this->after(),
			'autofocus'   => $this->autofocus(),
			'before'      => $this->before(),
			'default'     => $this->default(),
			'disabled'    => $this->isDisabled(),
			'help'        => $this->help(),
			'hidden'      => $this->isHidden(),
			'icon'        => $this->icon(),
			'label'       => $this->label(),
			'name'        => $this->name(),
			'placeholder' => $this->placeholder(),
			'required'    => $this->isRequired(),
			'saveable'    => $this->hasValue(),
			'translate'   => $this->translate(),
			'type'        => $this->type(),
			'when'        => $this->when(),
			'width'       => $this->width(),
		];
	}
}
