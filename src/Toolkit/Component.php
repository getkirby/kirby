<?php

namespace Kirby\Toolkit;

use Kirby\Exception\InvalidArgumentException;

class Component
{
    public static $types = [];

    protected $component;
    protected $attrs;
    protected $methods;
    protected $options;
    protected $type;

    public function __call(string $name, array $arguments = [])
    {
        if (isset($this->methods[$name]) === true) {
            return $this->methods[$name]->call($this, ...$arguments);
        }

        return $this->$name;
    }

    public function __construct(string $type, array $attrs = [])
    {
        if (isset(static::$types[$type]) === false) {
            throw new InvalidArgumentException('Undefined component type: ' . $type);
        }

        $this->attrs   = $attrs;
        $this->options = $options = static::$types[$type];
        $this->methods = $methods = $options['methods'] ?? [];

        foreach ($attrs as $attrName => $attrValue) {
            $this->$attrName = $attrValue;
        }

        if (isset($options['props']) === true) {
            $this->applyProps($options['props']);
        }

        if (isset($options['computed']) === true) {
            $this->applyComputed($options['computed']);
        }

        $this->attrs   = $attrs;
        $this->methods = $methods;
        $this->options = $options;
        $this->type    = $type;
    }

    public function __get(string $attr)
    {
        return null;
    }

    protected function applyProps(array $props)
    {
        foreach ($props as $propName => $propFunction) {
            if (isset($this->attrs[$propName]) === true) {
                $this->$propName = $propFunction($this->attrs[$propName]);
            } else {
                $this->$propName = $propFunction();
            }
        }
    }

    protected function applyComputed(array $computed)
    {
        foreach ($computed as $computedName => $computedFunction) {
            $this->$computedName = $computedFunction->call($this);
        }
    }

    public function toArray()
    {
        if (is_a($this->options['toArray'] ?? null, Closure::class) === true) {
            return $this->options['toArray']->call($this);
        }

        $array = [];

        foreach ($this->attrs ?? [] as $key => $value) {
            $array[$key] = $this->$key;
        }

        foreach ($this->options['props'] ?? [] as $key => $value) {
            $array[$key] = $this->$key;
        }

        foreach ($this->options['computed'] ?? [] as $key => $value) {
            $array[$key] = $this->$key;
        }

        ksort($array);

        return $array;
    }

}
