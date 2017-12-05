<?php

namespace Kirby\Cms;

use Closure;
use Exception;

class Object
{

    use HasPlugins;

    protected $schema = [];
    protected $props = [];

    public function __construct(array $props = [], array $schema = [])
    {
        $this->schema = $schema;
        $this->set(array_merge($this->defaults(), $props));
    }

    protected function defaults()
    {
        return array_map(function ($prop) {
            return $prop['default'] ?? null;
        }, $this->schema);
    }

    public function set($key, $value = null)
    {
        if (is_array($key) === true) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
            return $this;
        }

        if ($schema = ($this->schema[$key] ?? null)) {

            // inject the default value
            if ($value === null) {
                $value = $schema['default'] ?? null;
            }

            // check for required props
            if (($schema['required'] ?? false) === true && $value === null) {
                throw new Exception(sprintf('The "%s" prop is missing', $key));
            }

            // type validation
            if ($value !== null && is_a($value, Closure::class) === false) {
                $this->validateProp($key, $value);
            }

        }

        $this->props[$key] = $value;
        return $this;

    }

    protected function validateProp(string $key, $value): bool
    {

        $schema = $this->schema[$key] ?? [];
        $type   = $schema['type'] ?? null;
        $error  = 'The "%s" attribute must be of type "%s" not "%s"';

        if ($type === null) {
            return true;
        }

        if ($type === 'number') {
            if (is_numeric($value) !== true) {
                throw new Exception(sprintf($error, $key, $type, gettype($value)));
            }
        } elseif (is_object($value)) {
            if (is_a($value, $type) !== true) {
                throw new Exception(sprintf($error, $key, $type, get_class($value)));
            }
        } elseif ($type !== gettype($value)) {
            throw new Exception(sprintf($error, $key, $type, gettype($value)));
        }

        return true;

    }

    public function props(): array
    {
        return $this->props;
    }

    public function is(Object $object): bool
    {
        return $this->id() === $object->id();
    }

    protected function hasProp($key)
    {
        return isset($this->schema[$key]) === true || isset($this->props[$key]) === true;
    }

    protected function prop($key, array $arguments = [])
    {
        if (isset($this->props[$key]) === false) {
            return null;
        }

        if (is_a($this->props[$key], Closure::class)) {
            $value = $this->props[$key] = $this->props[$key]->call($this, ...$arguments);
            $this->validateProp($key, $value);
            return $this->props[$key] = $value;
        }

        return $this->props[$key];
    }

    public function __call($method, $arguments)
    {
        if ($this->hasPlugin($method)) {
            return $this->plugin($method, $arguments);
        }

        if ($this->hasProp($method)) {
            return $this->prop($method, $arguments);
        }

        return null;
    }

    public function toArray(): array
    {
        $array = [];

        foreach ($this->schema as $key => $options) {
            $array[$key] = $this->prop($key);
        }

        return $array;
    }

}
