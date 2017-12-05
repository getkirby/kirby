<?php

namespace Kirby\Form\Input;

use Closure;
use Kirby\Collection\Collection;

class SelectOptions extends Collection
{

    public function __construct(array $options = [])
    {
        $options = array_map(function ($option) {
            $option['text'] = $option['text'] ?? $option['value'] ?? null;
            return $option;
        }, $options);

        foreach ($options as $props) {
            $option = new Option($props);
            $this->append($option->value(), $option);
        }
    }

    public function select($value)
    {
        foreach ($this->data as $key => $option) {
            $this->data[$key]->set('selected', $value === $key);
        }
        return $this;
    }

    public function selected()
    {
        return $this->findBy('selected', true);
    }

    public function values(): array
    {
        return $this->keys();
    }

    public function toArray(Closure $map = null): array
    {
        return array_values(parent::toArray($map));
    }

    public function toHtml()
    {
        return implode($this->toArray(function ($option) {
            return $option->toHtml();
        }));
    }

    public function __toString(): string
    {
        return (string)$this->toHtml();
    }

}
