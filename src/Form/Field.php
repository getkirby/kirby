<?php

namespace Kirby\Form;

use Closure;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Component;
use Kirby\Toolkit\I18n;

/**
 * Form Field object that takes a Vue component style
 * array of properties and methods and converts them
 * to a usable field option array for the API.
 *
 * @package   Kirby Form
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Field extends Component
{
	use Mixin\Common;
	use Mixin\Decorators;
	use Mixin\Disabled;
	use Mixin\Endpoints;
	use Mixin\Model;
	use Mixin\Siblings;
	use Mixin\Translatable;
	use Mixin\Validation;
	use Mixin\When;
	use Mixin\Value;

	/**
	 * Registry for all component mixins
	 */
	public static array $mixins = [];

	/**
	 * Registry for all component types
	 */
	public static array $types = [];

	/**
	 * @throws \Kirby\Exception\InvalidArgumentException
	 */
	public function __construct(
		protected string $type,
		protected array $attrs = [],
		Fields|null $siblings = null
	) {
		if (isset(static::$types[$type]) === false) {
			throw new InvalidArgumentException(
				key: 'field.type.missing',
				data: [
					'name' => $attrs['name'] ?? '-',
					'type' => $type
				]
			);
		}

		$this->setModel($attrs['model'] ?? null);
		$this->setName($attrs['name'] ?? null);
		$this->setSiblings($attrs['siblings'] ?? null);
		$this->setValidate($attrs['validate'] ?? []);

		// unset the attrs that should no longer be handled
		// by the parent constructor, because we already took
		// care of them.
		unset(
			$attrs['model'],
			$attrs['name'],
			$attrs['siblings'],
			$attrs['type'],
			$attrs['validate']
		);

		parent::__construct($type, $attrs);
	}

	/**
	 * Returns field api routes
	 */
	public function api(): array
	{
		return $this->endpoints('api');
	}

	/**
	 * Get a component option from the original field component definition
	 */
	protected function componentOption(string $option, mixed $fallback = null): mixed
	{
		return static::setup($this->type)[$option] ?? $fallback;
	}

	/**
	 * Default props and computed of the field
	 */
	public static function defaults(): array
	{
		return [
			'props' => [
				/**
				 * Optional text that will be shown after the input
				 */
				'after' => function (array|string|null $after = null) {
					return I18n::translate($after, $after);
				},
				/**
				 * Sets the focus on this field when the form loads. Only the first field with this label gets
				 */
				'autofocus' => function (bool $autofocus = false): bool {
					return $autofocus;
				},
				/**
				 * Optional text that will be shown before the input
				 */
				'before' => function (array|string|null $before = null) {
					return I18n::translate($before, $before);
				},
				/**
				 * Default value for the field, which will be used when a page/file/user is created
				 */
				'default' => function (mixed $default = null) {
					return $default;
				},
				/**
				 * If `true`, the field is no longer editable and will not be saved
				 */
				'disabled' => function (bool $disabled = false): bool {
					return $disabled;
				},
				/**
				 * Optional help text below the field
				 */
				'help' => function (array|string|null $help = null) {
					return I18n::translate($help, $help);
				},
				/**
				 * Optional icon that will be shown at the end of the field
				 */
				'icon' => function (string|null $icon = null) {
					return $icon;
				},
				/**
				 * The field label can be set as string or associative array with translations
				 */
				'label' => function (array|string|null $label = null) {
					return I18n::translate($label, $label);
				},
				/**
				 * Optional placeholder value that will be shown when the field is empty
				 */
				'placeholder' => function (array|string|null $placeholder = null) {
					return I18n::translate($placeholder, $placeholder);
				},
				/**
				 * If `true`, the field has to be filled in correctly to be saved.
				 */
				'required' => function (bool $required = false): bool {
					return $required;
				},
				/**
				 * If `false`, the ield will be disabled in non-default languages and cannot be translated. This is only relevant in multi-language setups.
				 */
				'translate' => function (bool $translate = true): bool {
					return $translate;
				},
				/**
				 * Conditions when the field will be shown (since 3.1.0)
				 */
				'when' => function (array|null $when = null) {
					return $when;
				},
				/**
				 * The width of the field in the field grid. Available widths: `1/1`, `1/2`, `1/3`, `1/4`, `2/3`, `3/4`
				 */
				'width' => function (string|null $width = null) {
					return $width;
				},
				'value' => function (mixed $value = null) {
					return $value;
				}
			]
		];
	}

	/**
	 * Returns optional dialog routes for the field
	 */
	public function dialogs(): array
	{
		return $this->endpoints('dialogs');
	}

	/**
	 * Returns optional drawer routes for the field
	 */
	public function drawers(): array
	{
		return $this->endpoints('drawers');
	}

	/**
	 * Helper to get endpoint definitions from the field component options
	 */
	protected function endpoints(string $type): array
	{
		$endpoints = $this->componentOption($type, []);

		if ($endpoints instanceof Closure) {
			return $endpoints->call($this);
		}

		return $endpoints;
	}

	/**
	 * Creates a new field instance
	 */
	public static function factory(
		string $type,
		array $attrs = [],
		Fields|null $siblings = null
	): static|FieldClass {
		$field = static::$types[$type] ?? null;

		if (is_string($field) && class_exists($field) === true) {
			$attrs['siblings'] = $siblings;
			return new $field($attrs);
		}

		return new static($type, $attrs, $siblings);
	}

	/**
	 * Sets a new value for the field
	 */
	public function fill(mixed $value): static
	{
		// overwrite the attribute value
		$this->value = $this->attrs['value'] = $value;

		$props    = $this->componentOption('props', []);
		$computed = $this->componentOption('computed', []);

		// reevaluate the value prop
		$this->applyProp('value', $props['value'] ?? $value);

		// reevaluate the computed props
		$this->applyComputed($computed);

		// reset the errors cache
		$this->errors = null;

		return $this;
	}

	/**
	 * Checks if the field is hidden
	 */
	public function isHidden(): bool
	{
		return $this->componentOption('hidden', false) === true;
	}

	/**
	 * Checks if the field is saveable
	 */
	public function isSaveable(): bool
	{
		return $this->componentOption('save', true) !== false;
	}

	/**
	 * Return field props, which can be used in our
	 * frontend components
	 */
	public function props(): array
	{
		return [
			...parent::toArray(),
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
			'saveable'    => $this->isSaveable(),
			'translate'   => $this->translate(),
			'type'        => $this->type(),
			'when'        => $this->when(),
			'width'       => $this->width(),
		];
	}

	/**
	 * Returns the value of the field in a format to be stored by our storage classes
	 */
	public function toStoredValue(bool $default = false): mixed
	{
		$value = $this->toFormValue($default);
		$store = $this->componentOption('save', true);

		if ($store === false) {
			return null;
		}

		if ($store instanceof Closure) {
			return $store->call($this, $value);
		}

		return $value;
	}

	/**
	 * Defines all validation rules
	 */
	protected function validations(): array
	{
		return $this->componentOption('validations', []);
	}
}
