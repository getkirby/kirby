<?php

namespace Kirby\Form;

use Closure;
use Exception;
use Kirby\Cms\App;
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
	/**
	 * An array of all found errors
	 */
	protected array|null $errors = null;

	/**
	 * Parent collection with all fields of the current form
	 */
	protected Fields|null $formFields;

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
		Fields|null $formFields = null
	) {
		if (isset(static::$types[$type]) === false) {
			throw new InvalidArgumentException([
				'key'  => 'field.type.missing',
				'data' => ['name' => $attrs['name'] ?? '-', 'type' => $type]
			]);
		}

		if (isset($attrs['model']) === false) {
			throw new InvalidArgumentException('Field requires a model');
		}

		$this->formFields = $formFields;

		// use the type as fallback for the name
		$attrs['name'] ??= $type;
		$attrs['type']   = $type;

		parent::__construct($type, $attrs);
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
		Fields|null $formFields = null
	): static|FieldClass {
		$field = static::$types[$type] ?? null;

		if (is_string($field) && class_exists($field) === true) {
			$attrs['siblings'] = $formFields;
			return new $field($attrs);
		}

		return new static($type, $attrs, $formFields);
	}

	/**
	 * Parent collection with all fields of the current form
	 */
	public function formFields(): Fields|null
	{
		return $this->formFields;
	}

	/**
	 * Validates when run for the first time and returns any errors
	 */
	public function errors(): array
	{
		if ($this->errors === null) {
			$this->validate();
		}

		return $this->errors;
	}

	/**
	 * Checks if the field is empty
	 */
	public function isEmpty(mixed ...$args): bool
	{
		$value = match (count($args)) {
			0       => $this->value(),
			default => $args[0]
		};

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
	 * Checks if the field is invalid
	 */
	public function isInvalid(): bool
	{
		return empty($this->errors()) === false;
	}

	/**
	 * Checks if the field is required
	 */
	public function isRequired(): bool
	{
		return $this->required ?? false;
	}

	/**
	 * Checks if the field is valid
	 */
	public function isValid(): bool
	{
		return empty($this->errors()) === true;
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
	 * Checks if the field needs a value before being saved;
	 * this is the case if all of the following requirements are met:
	 * - The field is saveable
	 * - The field is required
	 * - The field is currently empty
	 * - The field is not currently inactive because of a `when` rule
	 */
	protected function needsValue(): bool
	{
		// check simple conditions first
		if (
			$this->save() === false ||
			$this->isRequired() === false ||
			$this->isEmpty() === false
		) {
			return false;
		}

		// check the data of the relevant fields if there is a `when` option
		if (
			empty($this->when) === false &&
			is_array($this->when) === true &&
			$formFields = $this->formFields()
		) {
			foreach ($this->when as $field => $value) {
				$field      = $formFields->get($field);
				$inputValue = $field?->value() ?? '';

				// if the input data doesn't match the requested `when` value,
				// that means that this field is not required and can be saved
				// (*all* `when` conditions must be met for this field to be required)
				if ($inputValue !== $value) {
					return false;
				}
			}
		}

		// either there was no `when` condition or all conditions matched
		return true;
	}

	/**
	 * Checks if the field is saveable
	 */
	public function save(): bool
	{
		return ($this->options['save'] ?? true) !== false;
	}

	/**
	 * Converts the field to a plain array
	 */
	public function toArray(): array
	{
		$array = parent::toArray();

		unset($array['model']);

		$array['hidden']    = $this->isHidden();
		$array['saveable']  = $this->save();
		$array['signature'] = md5(json_encode($array));

		ksort($array);

		return array_filter(
			$array,
			fn ($item) => $item !== null && is_object($item) === false
		);
	}

	/**
	 * Runs the validations defined for the field
	 */
	protected function validate(): void
	{
		$validations  = $this->options['validations'] ?? [];
		$this->errors = [];

		// validate required values
		if ($this->needsValue() === true) {
			$this->errors['required'] = I18n::translate('error.validation.required');
		}

		foreach ($validations as $key => $validation) {
			if (is_int($key) === true) {
				// predefined validation
				try {
					Validations::$validation($this, $this->value());
				} catch (Exception $e) {
					$this->errors[$validation] = $e->getMessage();
				}
				continue;
			}

			if ($validation instanceof Closure) {
				try {
					$validation->call($this, $this->value());
				} catch (Exception $e) {
					$this->errors[$key] = $e->getMessage();
				}
			}
		}

		if (
			empty($this->validate) === false &&
			($this->isEmpty() === false || $this->isRequired() === true)
		) {
			$rules  = A::wrap($this->validate);
			$errors = V::errors($this->value(), $rules);

			if (empty($errors) === false) {
				$this->errors = array_merge($this->errors, $errors);
			}
		}
	}

	/**
	 * Returns the value of the field if saveable
	 * otherwise it returns null
	 */
	public function value(): mixed
	{
		return $this->save() ? $this->value : null;
	}
}
