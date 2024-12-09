<?php

namespace Kirby\Form;

use Closure;
use Exception;
use Kirby\Cms\App;
use Kirby\Cms\HasSiblings;
use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;

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
 */
abstract class FieldClass
{
	use HasSiblings;

	protected string|null $after;
	protected bool $autofocus;
	protected string|null $before;
	protected mixed $default;
	protected bool $disabled;
	protected string|null $help;
	protected string|null $icon;
	protected string|null $label;
	protected ModelWithContent $model;
	protected string|null $name;
	protected string|null $placeholder;
	protected bool $required;
	protected Fields $siblings;
	protected bool $translate;
	protected mixed $value = null;
	protected array|null $when;
	protected string|null $width;

	public function __construct(
		protected array $params = []
	) {
		$this->setAfter($params['after'] ?? null);
		$this->setAutofocus($params['autofocus'] ?? false);
		$this->setBefore($params['before'] ?? null);
		$this->setDefault($params['default'] ?? null);
		$this->setDisabled($params['disabled'] ?? false);
		$this->setHelp($params['help'] ?? null);
		$this->setIcon($params['icon'] ?? null);
		$this->setLabel($params['label'] ?? null);
		$this->setModel($params['model'] ?? App::instance()->site());
		$this->setName($params['name'] ?? null);
		$this->setPlaceholder($params['placeholder'] ?? null);
		$this->setRequired($params['required'] ?? false);
		$this->setSiblings($params['siblings'] ?? null);
		$this->setTranslate($params['translate'] ?? true);
		$this->setWhen($params['when'] ?? null);
		$this->setWidth($params['width'] ?? null);

		if (array_key_exists('value', $params) === true) {
			$this->fill($params['value']);
		}
	}

	public function __call(string $param, array $args): mixed
	{
		if (isset($this->$param) === true) {
			return $this->$param;
		}

		return $this->params[$param] ?? null;
	}

	public function after(): string|null
	{
		return $this->stringTemplate($this->after);
	}

	public function api(): array
	{
		return $this->routes();
	}

	public function autofocus(): bool
	{
		return $this->autofocus;
	}

	public function before(): string|null
	{
		return $this->stringTemplate($this->before);
	}

	/**
	 * @deprecated 3.5.0
	 * @todo remove when the general field class setup has been refactored
	 *
	 * Returns the field data
	 * in a format to be stored
	 * in Kirby's content fields
	 */
	public function data(bool $default = false): mixed
	{
		return $this->store($this->value($default));
	}

	/**
	 * Returns the default value for the field,
	 * which will be used when a page/file/user is created
	 */
	public function default(): mixed
	{
		if (is_string($this->default) === false) {
			return $this->default;
		}

		return $this->stringTemplate($this->default);
	}

	/**
	 * Returns optional dialog routes for the field
	 */
	public function dialogs(): array
	{
		return [];
	}

	/**
	 * If `true`, the field is no longer editable and will not be saved
	 */
	public function disabled(): bool
	{
		return $this->disabled;
	}

	/**
	 * Returns optional drawer routes for the field
	 */
	public function drawers(): array
	{
		return [];
	}

	/**
	 * Runs all validations and returns an array of
	 * error messages
	 */
	public function errors(): array
	{
		return $this->validate();
	}

	/**
	 * Setter for the value
	 */
	public function fill(mixed $value = null): void
	{
		$this->value = $value;
	}

	/**
	 * Optional help text below the field
	 */
	public function help(): string|null
	{
		if (empty($this->help) === false) {
			$help = $this->stringTemplate($this->help);
			$help = $this->kirby()->kirbytext($help);
			return $help;
		}

		return null;
	}

	protected function i18n(string|array|null $param = null): string|null
	{
		return empty($param) === false ? I18n::translate($param, $param) : null;
	}

	/**
	 * Optional icon that will be shown at the end of the field
	 */
	public function icon(): string|null
	{
		return $this->icon;
	}

	public function id(): string
	{
		return $this->name();
	}

	public function isDisabled(): bool
	{
		return $this->disabled;
	}

	public function isEmpty(): bool
	{
		return $this->isEmptyValue($this->value());
	}

	public function isEmptyValue(mixed $value = null): bool
	{
		return in_array($value, [null, '', []], true);
	}

	public function isHidden(): bool
	{
		return false;
	}

	/**
	 * Checks if the field is invalid
	 */
	public function isInvalid(): bool
	{
		return $this->isValid() === false;
	}

	public function isRequired(): bool
	{
		return $this->required;
	}

	public function isSaveable(): bool
	{
		return true;
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
		return $this->model->kirby();
	}

	/**
	 * The field label can be set as string or associative array with translations
	 */
	public function label(): string
	{
		return $this->stringTemplate(
			$this->label ?? Str::ucfirst($this->name())
		);
	}

	/**
	 * Returns the parent model
	 */
	public function model(): ModelWithContent
	{
		return $this->model;
	}

	/**
	 * Returns the field name
	 */
	public function name(): string
	{
		return $this->name ?? $this->type();
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
			$this->isSaveable() === false ||
			$this->isRequired() === false ||
			$this->isEmpty()    === false
		) {
			return false;
		}

		// check the data of the relevant fields if there is a `when` option
		if (
			empty($this->when) === false &&
			is_array($this->when) === true &&
			$formFields = $this->siblings()
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
	 * Returns all original params for the field
	 */
	public function params(): array
	{
		return $this->params;
	}

	/**
	 * Optional placeholder value that will be shown when the field is empty
	 */
	public function placeholder(): string|null
	{
		return $this->stringTemplate($this->placeholder);
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
			'saveable'    => $this->isSaveable(),
			'translate'   => $this->translate(),
			'type'        => $this->type(),
			'when'        => $this->when(),
			'width'       => $this->width(),
		];
	}

	/**
	 * If `true`, the field has to be filled in correctly to be saved.
	 */
	public function required(): bool
	{
		return $this->required;
	}

	/**
	 * Routes for the field API
	 */
	public function routes(): array
	{
		return [];
	}

	/**
	 * @deprecated 3.5.0
	 * @todo remove when the general field class setup has been refactored
	 */
	public function save(): bool
	{
		return $this->isSaveable();
	}

	protected function setAfter(array|string|null $after = null): void
	{
		$this->after = $this->i18n($after);
	}

	protected function setAutofocus(bool $autofocus = false): void
	{
		$this->autofocus = $autofocus;
	}

	protected function setBefore(array|string|null $before = null): void
	{
		$this->before = $this->i18n($before);
	}

	protected function setDefault(mixed $default = null): void
	{
		$this->default = $default;
	}

	protected function setDisabled(bool $disabled = false): void
	{
		$this->disabled = $disabled;
	}

	protected function setHelp(array|string|null $help = null): void
	{
		$this->help = $this->i18n($help);
	}

	protected function setIcon(string|null $icon = null): void
	{
		$this->icon = $icon;
	}

	protected function setLabel(array|string|null $label = null): void
	{
		$this->label = $this->i18n($label);
	}

	protected function setModel(ModelWithContent $model): void
	{
		$this->model = $model;
	}

	protected function setName(string|null $name = null): void
	{
		$this->name = $name;
	}

	protected function setPlaceholder(array|string|null $placeholder = null): void
	{
		$this->placeholder = $this->i18n($placeholder);
	}

	protected function setRequired(bool $required = false): void
	{
		$this->required = $required;
	}

	protected function setSiblings(Fields|null $siblings = null): void
	{
		$this->siblings = $siblings ?? new Fields([$this]);
	}

	protected function setTranslate(bool $translate = true): void
	{
		$this->translate = $translate;
	}

	/**
	 * Setter for the when condition
	 */
	protected function setWhen(array|null $when = null): void
	{
		$this->when = $when;
	}

	/**
	 * Setter for the field width
	 */
	protected function setWidth(string|null $width = null): void
	{
		$this->width = $width;
	}

	/**
	 * Returns all sibling fields
	 */
	protected function siblingsCollection(): Fields
	{
		return $this->siblings;
	}

	/**
	 * Parses a string template in the given value
	 */
	protected function stringTemplate(string|null $string = null): string|null
	{
		if ($string !== null) {
			return $this->model->toString($string);
		}

		return null;
	}

	/**
	 * Converts the given value to a value
	 * that can be stored in the text file
	 */
	public function store(mixed $value): mixed
	{
		return $value;
	}

	/**
	 * Should the field be translatable?
	 */
	public function translate(): bool
	{
		return $this->translate;
	}

	/**
	 * Converts the field to a plain array
	 */
	public function toArray(): array
	{
		$props = $this->props();
		$props['signature'] = md5(json_encode($props));

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

	/**
	 * Runs the validations defined for the field
	 */
	protected function validate(): array
	{
		$validations = $this->validations();
		$value       = $this->value();
		$errors      = [];

		// validate required values
		if ($this->needsValue() === true) {
			$errors['required'] = I18n::translate('error.validation.required');
		}

		foreach ($validations as $key => $validation) {
			if (is_int($key) === true) {
				// predefined validation
				try {
					Validations::$validation($this, $value);
				} catch (Exception $e) {
					$errors[$validation] = $e->getMessage();
				}
				continue;
			}

			if ($validation instanceof Closure) {
				try {
					$validation->call($this, $value);
				} catch (Exception $e) {
					$errors[$key] = $e->getMessage();
				}
			}
		}

		return $errors;
	}

	/**
	 * Defines all validation rules
	 * @codeCoverageIgnore
	 */
	protected function validations(): array
	{
		return [];
	}

	/**
	 * Returns the value of the field if saveable
	 * otherwise it returns null
	 */
	public function value(bool $default = false): mixed
	{
		if ($this->isSaveable() === false) {
			return null;
		}

		if ($default === true && $this->isEmpty() === true) {
			return $this->default();
		}

		return $this->value;
	}

	protected function valueFromJson(mixed $value): array
	{
		try {
			return Data::decode($value, 'json');
		} catch (Throwable) {
			return [];
		}
	}

	protected function valueFromYaml(mixed $value): array
	{
		return Data::decode($value, 'yaml');
	}

	protected function valueToJson(
		array|null $value = null,
		bool $pretty = false
	): string {
		$constants = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;

		if ($pretty === true) {
			$constants |= JSON_PRETTY_PRINT;
		}

		return json_encode($value, $constants);
	}

	protected function valueToYaml(array|null $value = null): string
	{
		return Data::encode($value, 'yaml');
	}

	/**
	 * Conditions when the field will be shown
	 */
	public function when(): array|null
	{
		return $this->when;
	}

	/**
	 * Returns the width of the field in
	 * the Panel grid
	 */
	public function width(): string
	{
		return $this->width ?? '1/1';
	}
}
