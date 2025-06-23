<?php

namespace Kirby\Form;

use Closure;
use Kirby\Cms\HasSiblings;
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
 *
 * @use \Kirby\Cms\HasSiblings<\Kirby\Form\Fields>
 */
class Field extends Component
{
	use HasSiblings;
	use Mixin\Api;
	use Mixin\Model;
	use Mixin\Translatable;
	use Mixin\Validation;
	use Mixin\When;
	use Mixin\Value {
		isEmptyValue as protected isEmptyValueFromMixin;
	}

	/**
	 * Parent collection with all fields of the current form
	 */
	protected Fields $siblings;

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
		string $type,
		array $attrs = [],
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

		// use the type as fallback for the name
		$attrs['name'] ??= $type;
		$attrs['type']   = $type;

		// set the name to lowercase
		$attrs['name'] = strtolower($attrs['name']);

		$this->setModel($attrs['model'] ?? null);

		parent::__construct($type, $attrs);

		// set the siblings collection
		$this->siblings = $siblings ?? new Fields([$this]);
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
				'after' => function ($after = null) {
					return I18n::translate($after, $after);
				},
				/**
				 * Sets the focus on this field when the form loads. Only the first field with this label gets
				 */
				'autofocus' => function (bool|null $autofocus = null): bool {
					return $autofocus ?? false;
				},
				/**
				 * Optional text that will be shown before the input
				 */
				'before' => function ($before = null) {
					return I18n::translate($before, $before);
				},
				/**
				 * Default value for the field, which will be used when a page/file/user is created
				 */
				'default' => function ($default = null) {
					return $default;
				},
				/**
				 * If `true`, the field is no longer editable and will not be saved
				 */
				'disabled' => function (bool|null $disabled = null): bool {
					return $disabled ?? false;
				},
				/**
				 * Optional help text below the field
				 */
				'help' => function ($help = null) {
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
				'label' => function ($label = null) {
					return I18n::translate($label, $label);
				},
				/**
				 * Optional placeholder value that will be shown when the field is empty
				 */
				'placeholder' => function ($placeholder = null) {
					return I18n::translate($placeholder, $placeholder);
				},
				/**
				 * If `true`, the field has to be filled in correctly to be saved.
				 */
				'required' => function (bool|null $required = null): bool {
					return $required ?? false;
				},
				/**
				 * If `false`, the field will be disabled in non-default languages and cannot be translated. This is only relevant in multi-language setups.
				 */
				'translate' => function (bool $translate = true): bool {
					return $translate;
				},
				/**
				 * Conditions when the field will be shown (since 3.1.0)
				 */
				'when' => function ($when = null) {
					return $when;
				},
				/**
				 * The width of the field in the field grid, e.g. `1/1`, `1/2`, `1/3`, `1/4`, `2/3`, `3/4`
				 */
				'width' => function (string $width = '1/1') {
					return $width;
				},
				'value' => function ($value = null) {
					return $value;
				}
			],
			'computed' => [
				'after' => function () {
					/** @var \Kirby\Form\Field $this */
					if ($this->after !== null) {
						return $this->model()->toString($this->after);
					}
				},
				'before' => function () {
					/** @var \Kirby\Form\Field $this */
					if ($this->before !== null) {
						return $this->model()->toString($this->before);
					}
				},
				'default' => function () {
					/** @var \Kirby\Form\Field $this */
					if ($this->default === null) {
						return;
					}

					if (is_string($this->default) === false) {
						return $this->default;
					}

					return $this->model()->toString($this->default);
				},
				'help' => function () {
					/** @var \Kirby\Form\Field $this */
					if ($this->help) {
						$help = $this->model()->toSafeString($this->help);
						$help = $this->kirby()->kirbytext($help);
						return $help;
					}
				},
				'label' => function () {
					/** @var \Kirby\Form\Field $this */
					if ($this->label !== null) {
						return $this->model()->toString($this->label);
					}
				},
				'placeholder' => function () {
					/** @var \Kirby\Form\Field $this */
					if ($this->placeholder !== null) {
						return $this->model()->toString($this->placeholder);
					}
				}
			]
		];
	}

	/**
	 * Returns optional dialog routes for the field
	 */
	public function dialogs(): array
	{
		if (
			isset($this->options['dialogs']) === true &&
			$this->options['dialogs'] instanceof Closure
		) {
			return $this->options['dialogs']->call($this);
		}

		return [];
	}

	/**
	 * Returns optional drawer routes for the field
	 */
	public function drawers(): array
	{
		if (
			isset($this->options['drawers']) === true &&
			$this->options['drawers'] instanceof Closure
		) {
			return $this->options['drawers']->call($this);
		}

		return [];
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
		// remember the current state to restore it afterwards
		$attrs   = $this->attrs;
		$methods = $this->methods;
		$options = $this->options;
		$type    = $this->type;

		// overwrite the attribute value
		$this->value = $this->attrs['value'] = $value;

		// reevaluate the value prop
		$this->applyProp('value', $this->options['props']['value'] ?? $value);

		// reevaluate the computed props
		$this->applyComputed($this->options['computed'] ?? []);

		// restore the original state
		$this->attrs   = $attrs;
		$this->methods = $methods;
		$this->options = $options;
		$this->type    = $type;

		return $this;
	}

	/**
	 * @deprecated 5.0.0 Use `::siblings() instead
	 */
	public function formFields(): Fields
	{
		return $this->siblings;
	}

	/**
	 * Checks if the field has a value
	 */
	public function hasValue(): bool
	{
		return ($this->options['save'] ?? true) !== false;
	}

	/**
	 * Checks if the field is disabled
	 */
	public function isDisabled(): bool
	{
		return $this->disabled === true;
	}

	/**
	 * Checks if the given value is considered empty
	 */
	public function isEmptyValue(mixed $value = null): bool
	{
		if (
			isset($this->options['isEmpty']) === true &&
			$this->options['isEmpty'] instanceof Closure
		) {
			return $this->options['isEmpty']->call($this, $value);
		}

		return $this->isEmptyValueFromMixin($value);
	}

	/**
	 * Checks if the field is hidden
	 */
	public function isHidden(): bool
	{
		return ($this->options['hidden'] ?? false) === true;
	}

	/**
	 * Returns field api routes
	 */
	public function routes(): array
	{
		if (
			isset($this->options['api']) === true &&
			$this->options['api'] instanceof Closure
		) {
			return $this->options['api']->call($this);
		}

		return [];
	}

	/**
	 * Parent collection with all fields of the current form
	 */
	public function siblings(): Fields
	{
		return $this->siblings;
	}

	/**
	 * Returns all sibling fields for the HasSiblings trait
	 */
	protected function siblingsCollection(): Fields
	{
		return $this->siblings;
	}

	/**
	 * Converts the field to a plain array
	 */
	public function toArray(): array
	{
		$array = parent::toArray();

		unset($array['model']);

		$array['hidden']   = $this->isHidden();
		$array['saveable'] = $this->hasValue();

		ksort($array);

		return array_filter(
			$array,
			fn ($item) => $item !== null && is_object($item) === false
		);
	}

	/**
	 * Returns the value of the field in a format to be stored by our storage classes
	 */
	public function toStoredValue(): mixed
	{
		$value = $this->toFormValue();
		$store = $this->options['save'] ?? true;

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
		return $this->options['validations'] ?? [];
	}
}
