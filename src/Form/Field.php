<?php

namespace Kirby\Form;

use Closure;
use Exception;
use Kirby\Cms\App;
use Kirby\Cms\HasSiblings;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\A;
use Kirby\Toolkit\Component;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\V;

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
	use HasSiblings;
	use HasValidation;
	use HasWhenQuery;

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

		if (isset($attrs['model']) === false) {
			throw new InvalidArgumentException(
				message: 'Field requires a model'
			);
		}

		// use the type as fallback for the name
		$attrs['name'] ??= $type;
		$attrs['type']   = $type;

		parent::__construct($type, $attrs);

		// set the siblings collection
		$this->siblings = $siblings ?? new Fields([$this]);
	}

	/**
	 * Returns field api call
	 */
	public function api(): mixed
	{
		if (
			isset($this->options['api']) === true &&
			$this->options['api'] instanceof Closure
		) {
			return $this->options['api']->call($this);
		}

		return null;
	}

	/**
	 * Returns field data
	 */
	public function data(bool $default = false): mixed
	{
		$save = $this->options['save'] ?? true;

		if ($default === true && $this->isEmpty($this->value)) {
			$value = $this->default();
		} else {
			$value = $this->value;
		}

		if ($save === false) {
			return null;
		}

		if ($save instanceof Closure) {
			return $save->call($this, $value);
		}

		return $value;
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
				 * The width of the field in the field grid. Available widths: `1/1`, `1/2`, `1/3`, `1/4`, `2/3`, `3/4`
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
		// overwrite the attribute value
		$this->value = $this->attrs['value'] = $value;

		// reevaluate the value prop
		$this->applyProp('value', $this->options['props']['value'] ?? $value);

		// reevaluate the computed props
		$this->applyComputed($this->options['computed']);

		// reset the errors cache
		$this->errors = null;

		return $this;
	}

	/**
	 * @deprecated Use `::siblings() instead
	 */
	public function formFields(): Fields
	{
		return $this->siblings;
	}

	/**
	 * Checks if the field is disabled
	 */
	public function isDisabled(): bool
	{
		return $this->disabled === true;
	}

	/**
	 * Checks if the field is empty
	 * @deprecated Passing arguments is deprecated. Use `::isEmptyValue()` instead to check for
	 */
	public function isEmpty(mixed ...$args): bool
	{
		$value = match (count($args)) {
			0       => $this->value(),
			default => $args[0]
		};

		return $this->isEmptyValue($value);
	}

	/**
	 * Checks if the given value is considered empty
	 */
	public function isEmptyValue(mixed $value = null): bool
	{
		if ($empty = $this->options['isEmpty'] ?? null) {
			return $empty->call($this, $value);
		}

		return in_array($value, [null, '', []], true);
	}

	/**
	 * Checks if the field is hidden
	 */
	public function isHidden(): bool
	{
		return ($this->options['hidden'] ?? false) === true;
	}

	/**
	 * Checks if the field is required
	 */
	public function isRequired(): bool
	{
		return $this->required ?? false;
	}

	/**
	 * Checks if the field is saveable
	 */
	public function isSaveable(): bool
	{
		return ($this->options['save'] ?? true) !== false;
	}

	/**
	 * Returns the Kirby instance
	 */
	public function kirby(): App
	{
		return $this->model()->kirby();
	}

	/**
	 * Returns the parent model
	 */
	public function model(): mixed
	{
		return $this->model;
	}

	/**
	 * Checks if the field is saveable
	 * @deprecated Use `::isSaveable()` instead
	 */
	public function save(): bool
	{
		return $this->isSaveable();
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
		$array['saveable'] = $this->isSaveable();

		ksort($array);

		return array_filter(
			$array,
			fn ($item) => $item !== null && is_object($item) === false
		);
	}
	
	/**
	 * Defines all validation rules
	 */
	protected function validations(): array
	{
		return $this->options['validations'] ?? [];
	}

	/**
	 * Returns the value of the field if saveable
	 * otherwise it returns null
	 */
	public function value(): mixed
	{
		return $this->isSaveable() ? $this->value : null;
	}
}
