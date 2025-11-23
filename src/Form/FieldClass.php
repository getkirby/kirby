<?php

namespace Kirby\Form;

use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\HasI18n;

/**
 * Abstract field class to be used instead
 * of functional field components for more
 * control.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 *
 * @use \Kirby\Cms\HasSiblings<\Kirby\Form\Fields>
 */
abstract class FieldClass
{
	use HasI18n;
	use Mixin\After;
	use Mixin\Api;
	use Mixin\Autofocus;
	use Mixin\Before;
	use Mixin\DefaultValue;
	use Mixin\Disabled;
	use Mixin\Help;
	use Mixin\Icon;
	use Mixin\Label;
	use Mixin\Model;
	use Mixin\Name;
	use Mixin\Placeholder;
	use Mixin\Required;
	use Mixin\Siblings;
	use Mixin\Translatable;
	use Mixin\Validation;
	use Mixin\Value;
	use Mixin\When;
	use Mixin\Width;

	public function __construct(
		array|string|null $after = null,
		bool $autofocus = false,
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
		$this->setAfter($after);
		$this->setAutofocus($autofocus);
		$this->setBefore($before);
		$this->setDefault($default);
		$this->setDisabled($disabled);
		$this->setHelp($help);
		$this->setIcon($icon);
		$this->setLabel($label);
		$this->setName($name);
		$this->setPlaceholder($placeholder);
		$this->setRequired($required);
		$this->setTranslate($translate);
		$this->setWhen($when);
		$this->setWidth($width);
	}

	public function __call(string $param, array $args): mixed
	{
		if (property_exists($this, $param) === true) {
			return $this->$param;
		}

		throw new NotFoundException(message: 'Method or option "' . $param . '" does not exist for field type "' . $this->type() . '"');
	}

	/**
	 * Returns optional dialog routes for the field
	 */
	public function dialogs(): array
	{
		return [];
	}

	/**
	 * Returns optional drawer routes for the field
	 */
	public function drawers(): array
	{
		return [];
	}

	/**
	 * Creates a new field instance from a $props array
	 * @since 6.0.0
	 */
	public static function factory(
		array $props,
		Fields|null $siblings = null
	): static {
		$args = $props;

		unset(
			$args['model'],
			$args['type'],
			$args['value']
		);

		$field = new static(...$args);
		$field->setSiblings($siblings);

		if (array_key_exists('model', $props) === true) {
			$field->setModel($props['model']);
		}

		if (array_key_exists('value', $props) === true) {
			$field->fill($props['value']);
		}

		return $field;
	}

	public function id(): string
	{
		return $this->name();
	}

	public function isHidden(): bool
	{
		return false;
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

	/**
	 * @since 5.2.0
	 * @todo Move to `Value` mixin once array-based fields are unsupported
	 */
	public function reset(): static
	{
		$this->value = $this->emptyValue();
		return $this;
	}

	/**
	 * Parses a string template in the given value
	 */
	protected function stringTemplate(string|null $string = null): string|null
	{
		if ($string !== null) {
			return $this->model()->toString($string);
		}

		return null;
	}

	/**
	 * Converts the field to a plain array
	 */
	public function toArray(): array
	{
		$props = $this->props();

		ksort($props);

		return array_filter($props, fn ($item) => $item !== null);
	}

	/**
	 * Returns the field type
	 */
	public function type(): string
	{
		return lcfirst(basename(str_replace(['\\', 'Field'], ['/', ''], static::class)));
	}
}
