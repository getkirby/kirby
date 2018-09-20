<?php

namespace Kirby\Form;

use Exception;
use Kirby\Data\Yaml;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Component;
use Kirby\Toolkit\I18n;

/**
 * Form Field object that takes a Vue component style
 * array of properties and methods and converts them
 * to a usable field option array for the API.
 */
class Field extends Component
{

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
     * An array of all found errors
     *
     * @var array
     */
    protected $errors = [];

    public function __construct(string $type, array $attrs = [])
    {
        if (isset(static::$types[$type]) === false) {
            throw new InvalidArgumentException('The field type "' . $type . '" does not exist');
        }

        // use the type as fallback for the name
        $attrs['name'] = $attrs['name'] ?? $type;
        $attrs['type'] = $type;

        parent::__construct($type, $attrs);

        // apply the default value if the field is empty
        if ($this->isEmpty() === true) {
            $this->value = $this->default;
        }

        $this->validate();
    }

    protected function defaults(): array
    {
        return [
            'props' => [
                'after' => function ($after = null) {
                    return I18n::translate($after, $after);
                },
                'autofocus' => function (bool $autofocus = null): bool {
                    return $autofocus ?? false;
                },
                'before' => function ($before = null) {
                    return I18n::translate($before, $before);
                },
                'default' => function ($default = null) {
                    return $default;
                },
                'disabled' => function (bool $disabled = null): bool {
                    return $disabled ?? false;
                },
                'help' => function ($help = null) {
                    return I18n::translate($help, $help);
                },
                'icon' => function (string $icon = null) {
                    return $icon;
                },
                'label' => function ($label = null) {
                    return I18n::translate($label, $label);
                },
                'placeholder' => function ($placeholder = null) {
                    return I18n::translate($placeholder, $placeholder);
                },
                'required' => function (bool $required = null): bool {
                    return $required ?? false;
                },
                'width' => function (string $width = '1/1') {
                    return $width;
                },
                'value' => function ($value = null) {
                    return $value;
                }
            ]
        ];
    }

    public function errors(): array
    {
        return $this->errors;
    }

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

    public function isInvalid(): bool
    {
        return empty($this->errors) === false;
    }

    public function isRequired(): bool
    {
        return $this->required ?? false;
    }

    public function isValid(): bool
    {
        return empty($this->errors) === true;
    }

    public function kirby()
    {
        return $this->model->kirby();
    }

    public function model()
    {
        return $this->model;
    }

    public function save(): bool
    {
        return $this->options['save'] ?? true;
    }

    public function toArray(): array
    {
        $array = parent::toArray();

        unset($array['model']);

        $array['invalid']   = $this->isInvalid();
        $array['errors']    = $this->errors();
        $array['signature'] = md5(json_encode($array));

        ksort($array);

        return array_filter($array, function ($item) {
            return $item !== null;
        });
    }

    public function toString(): ?string
    {
        if ($this->save() === false) {
            return null;
        }

        $value = $this->value;

        if (isset($this->options['toString']) === true) {
            return $this->options['toString']->call($this, $value);
        }

        // DEPRECATED
        if (isset($this->methods['toString']) === true) {
            return $this->methods['toString']->call($this, $value);
        }

        if (is_array($value) === true) {
            return Yaml::encode($value);
        }

        if (is_object($value) === true) {
            throw new Exception('The field value cannot be converted to a string');
        }

        return (string)$value;
    }

    protected function validate()
    {
        $validations  = $this->options['validations'] ?? [];
        $this->errors = [];

        // validate required values
        if ($this->isRequired() === true && $this->save() === true && $this->isEmpty() === true) {
            $this->errors['required'] = I18n::translate('error.form.field.required', 'The field is required');
        }

        // no further validations? fine!
        if (empty($validations) === true) {
            return true;
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
    }

    public function value()
    {
        return $this->save() ? $this->value : null;
    }
}
