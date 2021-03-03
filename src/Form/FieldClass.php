<?php

namespace Kirby\Form;

use Exception;
use Kirby\Cms\HasSiblings;
use Kirby\Cms\ModelWithContent;
use Kirby\Data\Data;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;

abstract class FieldClass
{
    use HasSiblings;

    protected $after;
    protected $autofocus;
    protected $before;
    protected $default;
    protected $disabled;
    protected $help;
    protected $icon;
    protected $label;
    /**
     * @var \Kirby\Cms\ModelWithContent
     */
    protected $model;
    protected $name;
    protected $params;
    protected $placeholder;
    protected $required;
    protected $siblings;
    protected $translate;
    protected $value;
    protected $when;
    protected $width;

    public function __call(string $param, array $args)
    {
        if (isset($this->$param) === true) {
            return $this->$param;
        }

        return $this->params[$param] ?? null;
    }

    public function __construct(array $params = [])
    {
        $this->params = $params;

        $this->setAfter($params['after'] ?? null);
        $this->setAutofocus($params['autofocus'] ?? false);
        $this->setBefore($params['before'] ?? null);
        $this->setDefault($params['default'] ?? null);
        $this->setDisabled($params['disabled'] ?? false);
        $this->setHelp($params['help'] ?? null);
        $this->setIcon($params['icon'] ?? null);
        $this->setLabel($params['label'] ?? null);
        $this->setModel($params['model'] ?? site());
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

    public function after(): ?string
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

    public function before(): ?string
    {
        return $this->stringTemplate($this->before);
    }

    /**
     * @deprecated
     *
     * Returns the field data
     * in a format to be stored
     * in Kirby's content fields
     *
     * @param bool $default
     * @return mixed
     */
    public function data(bool $default = false)
    {
        return $this->store($this->value($default));
    }

    /**
     * Returns the default value for the field,
     * which will be used when a page/file/user is created
     */
    public function default()
    {
        if ($this->default === null) {
            return;
        }

        if (is_string($this->default) === false) {
            return $this->default;
        }

        return $this->stringTemplate($this->default);
    }

    /**
     * If `true`, the field is no longer editable and will not be saved
     */
    public function disabled(): bool
    {
        return $this->disabled;
    }

    /**
     * Optional help text below the field
     */
    public function help(): ?string
    {
        if (empty($this->help) === false) {
            $help = $this->stringTemplate($this->help);
            $help = $this->kirby()->kirbytext($help);
            return $help;
        }

        return null;
    }

    protected function i18n($param)
    {
        return empty($param) === false ? I18n::translate($param, $param) : null;
    }

    /**
     * Optional icon that will be shown at the end of the field
     */
    public function icon(): ?string
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

    public function isEmptyValue($value): bool
    {
        return in_array($value, [null, '', []], true);
    }

    /**
     * Checks if the field is invalid
     *
     * @return bool
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
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return empty($this->errors()) === true;
    }

    /**
     * Runs all validations and returns an array of
     * error messages
     *
     * @return array
     */
    public function errors(): array
    {
        return $this->validate();
    }

    /**
     * Setter for the value
     *
     * @param mixed $value
     * @return void
     */
    public function fill($value = null)
    {
        $this->value = $value;
    }

    /**
     * Returns the Kirby instance
     *
     * @return \Kirby\Cms\App
     */
    public function kirby()
    {
        return $this->model->kirby();
    }

    /**
     * The field label can be set as string or associative array with translations
     */
    public function label(): string
    {
        return $this->stringTemplate($this->label ?? Str::ucfirst($this->name()));
    }

    /**
     * Returns the parent model
     *
     * @return mixed|null
     */
    public function model()
    {
        return $this->model;
    }

    /**
     * Returns the field name
     *
     * @return string
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
     *
     * @return bool
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
        if (empty($this->when) === false && is_array($this->when) === true) {
            $formFields = $this->siblings();

            if ($formFields !== null) {
                foreach ($this->when as $field => $value) {
                    $field      = $formFields->get($field);
                    $inputValue = $field !== null ? $field->value() : '';

                    // if the input data doesn't match the requested `when` value,
                    // that means that this field is not required and can be saved
                    // (*all* `when` conditions must be met for this field to be required)
                    if ($inputValue !== $value) {
                        return false;
                    }
                }
            }
        }

        // either there was no `when` condition or all conditions matched
        return true;
    }

    /**
     * Returns all original params for the field
     *
     * @return array
     */
    public function params(): array
    {
        return $this->params;
    }

    /**
     * Optional placeholder value that will be shown when the field is empty
     */
    public function placeholder(): ?string
    {
        return $this->stringTemplate($this->placeholder);
    }

    /**
     * Define the props that will be sent to
     * the Vue component
     *
     * @return array
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
     *
     * @return bool
     */
    public function required(): bool
    {
        return $this->required;
    }

    /**
     * Routes for the field API
     *
     * @return array
     */
    public function routes(): array
    {
        return [];
    }

    /**
     * @deprecated
     *
     * @return bool
     */
    public function save()
    {
        return $this->isSaveable();
    }

    protected function setAfter($after = null)
    {
        $this->after = $this->i18n($after);
    }

    protected function setAutofocus(bool $autofocus = false)
    {
        $this->autofocus = $autofocus;
    }

    protected function setBefore($before = null)
    {
        $this->before = $this->i18n($before);
    }

    protected function setDefault($default = null)
    {
        $this->default = $default;
    }

    protected function setDisabled(bool $disabled = false)
    {
        $this->disabled = $disabled;
    }

    protected function setHelp($help = null)
    {
        $this->help = $this->i18n($help);
    }

    protected function setIcon(string $icon = null)
    {
        $this->icon = $icon;
    }

    protected function setLabel($label = null)
    {
        $this->label = $this->i18n($label);
    }

    protected function setModel(ModelWithContent $model)
    {
        $this->model = $model;
    }

    protected function setName(string $name = null)
    {
        $this->name = $name;
    }

    protected function setPlaceholder($placeholder = null)
    {
        $this->placeholder = $this->i18n($placeholder);
    }

    protected function setRequired(bool $required = false)
    {
        $this->required = $required;
    }

    protected function setSiblings(Fields $siblings = null)
    {
        $this->siblings = $siblings ?? new Fields([]);
    }

    protected function setTranslate(bool $translate = true)
    {
        $this->translate = $translate;
    }

    protected function setWhen($when = null)
    {
        $this->when = $when;
    }

    protected function setWidth(string $width = null)
    {
        $this->width = $width;
    }

    protected function siblingsCollection()
    {
        return $this->siblings;
    }

    protected function stringTemplate(?string $string = null): ?string
    {
        if ($string !== null) {
            return $this->model->toString($string);
        }

        return null;
    }

    public function store($value)
    {
        return $value;
    }

    /**
     * Should the field be translatable?
     *
     * @return bool
     */
    public function translate(): bool
    {
        return $this->translate;
    }

    /**
     * Converts the field to a plain array
     *
     * @return array
     */
    public function toArray(): array
    {
        $props = $this->props();
        $props['signature'] = md5(json_encode($props));

        ksort($props);

        return array_filter($props, function ($item) {
            return $item !== null;
        });
    }

    public function type(): string
    {
        return lcfirst(basename(str_replace(['\\', 'Field'], ['/', ''], static::class)));
    }

    /**
     * Runs the validations defined for the field
     *
     * @return array
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

            if (is_a($validation, 'Closure') === true) {
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
     *
     * @return array
     */
    protected function validations(): array
    {
        return [];
    }

    /**
     * Returns the value of the field if saveable
     * otherwise it returns null
     *
     * @return mixed
     */
    public function value(bool $default = false)
    {
        if ($this->isSaveable() === false) {
            return null;
        }

        if ($default === true && $this->isEmpty() === true) {
            return $this->default();
        }

        return $this->value;
    }

    protected function valueFromJson($value): array
    {
        try {
            return Data::decode($value, 'json');
        } catch (Throwable $e) {
            return [];
        }
    }

    protected function valueFromYaml($value)
    {
        return Data::decode($value, 'yaml');
    }

    protected function valueToJson(array $value = null, bool $pretty = false): string
    {
        if ($pretty === true) {
            return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return json_encode($value);
    }

    protected function valueToYaml(array $value = null): string
    {
        return Data::encode($value, 'yaml');
    }

    /**
     * Conditions when the field will be shown
     *
     * @return array|null
     */
    public function when(): ?array
    {
        return $this->when;
    }

    /**
     * Returns the width of the field in
     * the Panel grid
     *
     * @return string
     */
    public function width(): string
    {
        return $this->width ?? '1/1';
    }
}
