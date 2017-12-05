<?php

namespace Kirby\Form;

use Kirby\Cms\Object;
use Kirby\Html\Element;

abstract class Component extends Object
{

    public function __construct(array $props)
    {
        parent::__construct($props, $this->schema());
    }

    public function tag()
    {
        return null;
    }

    public function schema(...$extend): array
    {
        return array_replace_recursive([], ...$extend);
    }

    public function attributes(): array
    {
        $attributes = [];

        foreach ($this->schema() as $key => $options) {
            if (($options['attribute'] ?? false) === true) {
                $attributes[$key] = $this->prop($key);
            }
        }

        return $attributes;
    }

    public function element()
    {
        if ($this->tag()) {
            return new Element($this->tag(), $this->attributes());
        }

        return null;
    }

    public function toHtml()
    {
        return $this->element();
    }

    public function toArray(): array
    {
        $array = [];

        foreach ($this->schema() as $key => $value) {
            $array[$key] = $this->prop($key);
        }

        ksort($array);

        return $array;
    }

    public function toString(): string
    {
        return (string)$this->toHtml();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

}
