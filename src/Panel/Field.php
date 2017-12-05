<?php

namespace Kirby\Panel;

use Exception;
use Kirby\Cms\Object;
use Kirby\Toolkit\V;

class Field
{
    protected static $types = [];

    protected $model;
    protected $props;
    protected $value;

    public function __construct(Object $model, $value, array $props)
    {

        if (isset($props['type']) === false) {
            throw new Exception('The field type is missing');
        }

        $this->model         = $model;
        $this->originalValue = $value;
        $this->handler       = static::type($props['type']);

        try {
            $this->props = array_merge($props, $this->handle('props', $props));
        } catch (Exception $e) {
            $this->props = $props;
        }

    }

    public function props(): array
    {
        return $this->props;
    }

    public function prop(string $name)
    {
        return $this->props[$name] ?? null;
    }

    public function model()
    {
        return $this->model;
    }

    public function originalValue()
    {
        return $this->originalValue;
    }

    public function handle($method, ...$arguments)
    {
        if ($this->handler === null || isset($this->handler[$method]) === false) {
            throw new Exception('The field handler does not exist');
        }

        return $this->handler[$method]->call($this, ...$arguments);
    }

    public function name(): string
    {
        return $this->prop('name');
    }

    public function value()
    {
        $value = $this->originalValue();

        if ($value === null || $value === '') {
            $value = $this->prop('default');
        }

        try {
            return $this->handle('value', $value);
        } catch (Exception $e) {
            return $value;
        }
    }

    public function result($input)
    {
        try {
            return $this->handle('result', $input);
        } catch (Exception $e) {
            return $input;
        }
    }

    public function validate($input)
    {

        if ($this->prop('required') === true) {
            if ($input === null || $input === '') {
                return false;
            }
        }

        if ($rules = $this->prop('validate')) {
            try {
                V::value($input, $rules);
            } catch (Exception $e) {
                return false;
            }
        }

        try {
            return $this->handle('validate', $input);
        } catch (Exception $e) {
            return true;
        }
    }

    public static function type(string $name, array $props = null)
    {
        if ($props === null) {
            return static::$types[$name] ?? null;
        }

        return static::$types[$name] = $props;
    }

    public function toArray(): array
    {
        return array_merge($this->props, ['value' => $this->value()]);
    }

}
