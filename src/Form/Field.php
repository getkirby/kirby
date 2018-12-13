<?php

namespace Kirby\Form;

use Exception;
use Kirby\Data\Yaml;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Http\Router;
use Kirby\Toolkit\Component;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\V;

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

        $this->validate();
    }

    public function api()
    {
        if (isset($this->options['api']) === true && is_callable($this->options['api']) === true) {
            return $this->options['api']->call($this);
        }
    }

    public function data($default = false)
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
                 * Default value for the field, which will be used when a Page/File/User is created
                 */
                'default' => function ($default = null) {
                    return $default;
                },
                /**
                 * If true, the field is no longer editable and will not be saved
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
                 * If true, the field has to be filled in correctly to be saved.
                 */
                'required' => function (bool $required = null): bool {
                    return $required ?? false;
                },
                /**
                 * If false, the field will be disabled in non-default languages and cannot be translated. This is only relevant in multi-language setups.
                 */
                'translate' => function (bool $translate = true): bool {
                    return $translate;
                },
                /**
                 * The width of the field in the field grid. Available widths: 1/1, 1/2, 1/3, 1/4, 2/3, 3/4
                 */
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
        return ($this->options['save'] ?? true) !== false;
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

    protected function validate()
    {
        $validations  = $this->options['validations'] ?? [];
        $this->errors = [];

        // validate required values
        if ($this->isRequired() === true && $this->save() === true && $this->isEmpty() === true) {
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

        if (empty($this->validate) === false) {
            $errors = V::errors($this->value(), $this->validate);

            if (empty($errors) === false) {
                $this->errors = array_merge($this->errors, $errors);
            }
        }
    }

    public function value()
    {
        return $this->save() ? $this->value : null;
    }
}
