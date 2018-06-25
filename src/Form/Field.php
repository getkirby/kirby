<?php

namespace Kirby\Form;

use ArgumentCountError;
use Closure;
use Exception;
use TypeError;
use Kirby\Data\Yaml;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\I18n;

/**
 * Form Field object that takes a Vue component style
 * array of properties and methods and converts them
 * to a usable field option array for the API.
 */
class Field
{
    public static $mixins = [];
    public static $types = [];

    protected $attrs;
    protected $computed = [];
    protected $data = [];
    protected $definition;
    protected $errors = [];
    protected $methods;
    protected $name;
    protected $props;
    protected $type;

    public function __call(string $method, array $args = [])
    {
        if (isset($this->computed[$method])) {
            return $this->computed[$method];
        }

        if (isset($this->props[$method])) {
            return $this->props[$method];
        }

        if (isset($this->methods[$method])) {
            return $this->methods[$method]->call($this, ...$args);
        }

        if (isset($this->data[$method])) {
            return $this->data[$method];
        }
    }

    public function __construct(array $attrs, array $data = [])
    {
        if (isset($attrs['type'], $attrs['name']) === false) {
            throw new InvalidArgumentException('You must define a name and type for the field');
        }

        $this->type  = $attrs['type'];
        $this->name  = $attrs['name'];
        $this->data  = $data;
        $this->attrs = $attrs;

        if (isset(static::$types[$this->type]) === false) {
            throw new InvalidArgumentException('The field type "' . $this->type . '" does not exist');
        }

        $this->definition = $this->define($this->type);
        $this->methods    = $this->definition['methods'] ?? [];
        $this->props      = $this->resolveProps($this->definition['props'], $this->attrs);
        $this->computed   = $this->resolveComputed($this->definition['computed'] ?? []);

        $this->applyDefaultValue();
        $this->validate();
    }

    protected function applyDefaultValue()
    {
        if ($this->isEmpty($this->props['value']) === true) {
            $this->props['value'] = $this->definition['props']['value']->call($this, $this->default());
        }
    }

    public static function defaults(): array
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
                'placeholder' => function (string $placeholder = null) {
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

    public function define(string $type): array
    {
        $definition = static::typeDefinition($type);

        // resolve mixins
        if (isset($definition['mixins']) === true) {
            foreach ($definition['mixins'] as $mixin) {
                $definition = array_replace_recursive(static::mixinDefinition($mixin), $definition);
            }
        }

        return $definition;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function isEmpty($value): bool
    {
        if (isset($this->methods['isEmpty']) === true) {
            return $this->methods['isEmpty']->call($this, $value);
        }

        return in_array($value, [null, '', []], true);
    }

    public function isInvalid(): bool
    {
        return empty($this->errors) === false;
    }

    public function isRequired(): bool
    {
        return $this->__call('required');
    }

    public function isValid(): bool
    {
        return empty($this->errors) === true;
    }

    protected static function mixinDefinition(string $mixin): array
    {
        $definition = static::$mixins[$mixin] ?? null;

        if (is_string($definition) && file_exists($definition)) {
            $definition = static::$mixins[$mixin] = include $definition;
        }

        if (is_array($definition) === false) {
            throw new InvalidArgumentException('The mixin definition for "' . $mixin . '" is invalid');
        }

        return $definition;
    }

    protected function resolveComputed($computed)
    {
        $result = [];

        foreach ($computed as $name => $callback) {
            $result[$name] = $callback->call($this);
        }

        return $result;
    }

    protected function resolveProps($props, $attrs)
    {
        $result = [];

        foreach ($props as $name => $callback) {
            if (is_a($callback, Closure::class) === false) {
                $result[$name] = $callback;
                continue;
            }

            try {
                if (isset($attrs[$name]) === true) {
                    $result[$name] = $callback->call($this, $attrs[$name]);
                } else {
                    $result[$name] = $callback->call($this);
                }
            } catch (ArgumentCountError $e) {
                throw new Exception('The "' . $name . '" field property is required');
            }
        }

        // merge the other attributes
        $result = array_merge($attrs, $result);

        return $result;
    }

    public function save()
    {
        return $this->definition['save'] ?? true;
    }

    public function toArray(): array
    {
        $array = array_merge($this->props, $this->computed);
        ksort($array);

        $array['invalid'] = $this->isInvalid();
        $array['errors']  = $this->errors();

        return array_filter($array, function ($item) {
            return $item !== null;
        });
    }

    public function toString(): string
    {
        $value = $this->props['value'];

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

    protected static function typeDefinition(string $type): array
    {
        $definition = static::$types[$type] ?? null;

        if (is_string($definition) && file_exists($definition)) {
            $definition = static::$types[$type] = include $definition;
        }

        if (is_array($definition) === false) {
            throw new InvalidArgumentException('The field definition for "' . $type . '" is invalid');
        }

        return array_replace_recursive(static::defaults(), $definition);
    }

    protected function validate()
    {
        $validations  = $this->definition['validations'] ?? [];
        $this->errors = [];

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

            if (is_a($validation, Closure::class) === true) {
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
        return $this->save() ? $this->__call("value") : null;
    }
}
