<?php

namespace Kirby\Form;

use Exception;
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
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class Field extends Component
{
    /**
     * An array of all found errors
     *
     * @var array|null
     */
    protected $errors;

    /**
     * Parent collection with all fields of the current form
     *
     * @var \Kirby\Form\Fields|null
     */
    protected $formFields;

    /**
     * Registry for all component mixins
     *
     * @var array
     */
    public static $mixins = [];

    /**
     * Registry for all component types
     *
     * @var array
     */
    public static $types = [];

    /**
     * Field constructor
     *
     * @param string $type
     * @param array $attrs
     * @param \Kirby\Form\Fields|null $formFields
     * @throws \Kirby\Exception\InvalidArgumentException
     */
    public function __construct(string $type, array $attrs = [], ?Fields $formFields = null)
    {
        if (isset(static::$types[$type]) === false) {
            throw new InvalidArgumentException('The field type "' . $type . '" does not exist');
        }

        if (isset($attrs['model']) === false) {
            throw new InvalidArgumentException('Field requires a model');
        }

        $this->formFields = $formFields;

        // use the type as fallback for the name
        $attrs['name'] = $attrs['name'] ?? $type;
        $attrs['type'] = $type;

        parent::__construct($type, $attrs);
    }

    /**
     * Returns field api call
     *
     * @return mixed
     */
    public function api()
    {
        if (isset($this->options['api']) === true && is_callable($this->options['api']) === true) {
            return $this->options['api']->call($this);
        }
    }

    /**
     * Returns field data
     *
     * @param bool $default
     * @return mixed
     */
    public function data(bool $default = false)
    {
        $save = $this->options['save'] ?? true;

        if ($default === true && $this->isEmpty($this->value)) {
            $value = $this->default();
        } else {
            $value = $this->value;
        }

        if ($save === false) {
            return null;
        } elseif (is_callable($save) === true) {
            return $save->call($this, $value);
        } else {
            return $value;
        }
    }

    /**
     * Default props and computed of the field
     *
     * @return array
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
                'autofocus' => function (bool $autofocus = null): bool {
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
                'disabled' => function (bool $disabled = null): bool {
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
                'icon' => function (string $icon = null) {
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
                'required' => function (bool $required = null): bool {
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
                    if ($this->after !== null) {
                        return $this->model()->toString($this->after);
                    }
                },
                'before' => function () {
                    if ($this->before !== null) {
                        return $this->model()->toString($this->before);
                    }
                },
                'default' => function () {
                    if ($this->default === null) {
                        return;
                    }

                    if (is_string($this->default) === false) {
                        return $this->default;
                    }

                    return $this->model()->toString($this->default);
                },
                'help' => function () {
                    if ($this->help) {
                        $help = $this->model()->toString($this->help);
                        $help = $this->kirby()->kirbytext($help);
                        return $help;
                    }
                },
                'label' => function () {
                    if ($this->label !== null) {
                        return $this->model()->toString($this->label);
                    }
                },
                'placeholder' => function () {
                    if ($this->placeholder !== null) {
                        return $this->model()->toString($this->placeholder);
                    }
                }
            ]
        ];
    }

    /**
     * Creates a new field instance
     *
     * @param string $type
     * @param array $attrs
     * @param Fields|null $formFields
     * @return static
     */
    public static function factory(string $type, array $attrs = [], ?Fields $formFields = null)
    {
        $field = static::$types[$type] ?? null;

        if (is_string($field) && class_exists($field) === true) {
            $attrs['siblings'] = $formFields;
            return new $field($attrs);
        }

        return new static($type, $attrs, $formFields);
    }

    /**
     * Parent collection with all fields of the current form
     *
     * @return \Kirby\Form\Fields|null
     */
    public function formFields(): ?Fields
    {
        return $this->formFields;
    }

    /**
     * Validates when run for the first time and returns any errors
     *
     * @return array
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
     *
     * @param mixed ...$args
     * @return bool
     */
    public function isEmpty(...$args): bool
    {
        if (count($args) === 0) {
            $value = $this->value();
        } else {
            $value = $args[0];
        }

        if (isset($this->options['isEmpty']) === true) {
            return $this->options['isEmpty']->call($this, $value);
        }

        return in_array($value, [null, '', []], true);
    }

    /**
     * Checks if the field is invalid
     *
     * @return bool
     */
    public function isInvalid(): bool
    {
        return empty($this->errors()) === false;
    }

    /**
     * Checks if the field is required
     *
     * @return bool
     */
    public function isRequired(): bool
    {
        return $this->required ?? false;
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
     * Returns the Kirby instance
     *
     * @return \Kirby\Cms\App
     */
    public function kirby()
    {
        return $this->model->kirby();
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
        if ($this->save() === false || $this->isRequired() === false || $this->isEmpty() === false) {
            return false;
        }

        // check the data of the relevant fields if there is a `when` option
        if (empty($this->when) === false && is_array($this->when) === true) {
            $formFields = $this->formFields();

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
     * Checks if the field is saveable
     *
     * @return bool
     */
    public function save(): bool
    {
        return ($this->options['save'] ?? true) !== false;
    }

    /**
     * Converts the field to a plain array
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        unset($array['model']);

        $array['saveable']  = $this->save();
        $array['signature'] = md5(json_encode($array));

        ksort($array);

        return array_filter($array, function ($item) {
            return $item !== null;
        });
    }

    /**
     * Runs the validations defined for the field
     *
     * @return void
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

            if (is_a($validation, 'Closure') === true) {
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
     *
     * @return mixed
     */
    public function value()
    {
        return $this->save() ? $this->value : null;
    }
}
