<?php

namespace Kirby\Form;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;

class Field
{

    use HasOptions;
    use HasValue;

    public static $types = [];

    protected $props;
    protected $methods;

    public function __call(string $name, array $arguments)
    {
        if ($this->props()->has($name) === true) {
            return $this->props()->{$name}(...$arguments);
        }

        if ($this->methods()->has($name) === true) {
            return $this->methods()->{$name}(...$arguments);
        }

        return null;
    }

    public function __construct(array $props, string $field)
    {
        $definition = require $field;

        $this->props = new FieldProps($this, $definition['props'] ?? []);
        $this->methods = new FieldMethods($this, $definition['methods'] ?? []);

        // Separate value from other arguments
        $this->setValue($props['value'] ?? null);
        unset($props['value']);

        $this->props()->setProps($props);
    }


    public static function factory(array $props)
    {
        if (isset($props['type']) === false) {
            throw new NotFoundException('Missing field type');
        }

        // ensure that type prop is first
        $props = array_merge(
            ['type' =>  $props['type']],
            $props
        );

        if (isset(static::$types[$props['type']]) === false) {
            throw new InvalidArgumentException(sprintf('Invalid field type: "%s"', $props['type']));
        }

        return new static($props, static::$types[$props['type']]);
    }

    public function isDisabled(): bool
    {
        return $this->disabled();
    }

    public function methods(): FieldMethods
    {
        return $this->methods;
    }

    public function props(): FieldProps
    {
        return $this->props;
    }

    public function toArray(): array
    {
        $array = array_merge(
            $this->props()->toArray(),
            ['value' => $this->value()]
        );

        unset($array['model']);

        // keep it tidy
        ksort($array);

        return $array;
    }

    public function validate(string $validator, string $prop = 'value')
    {
        return Validate::{$validator}($this, $prop);
    }
}
