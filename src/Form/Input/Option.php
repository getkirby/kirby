<?php

namespace Kirby\Form\Input;

use Kirby\Form\Input;
use Kirby\Html\Element;

class Option extends Input
{

    public function tag(): string
    {
        return 'option';
    }

    public function schema(...$extend): array
    {
        return array_replace_recursive([
            'disabled' => [
                'type'      => 'boolean',
                'default'   => false,
                'attribute' => true
            ],
            'selected' => [
                'type'      => 'boolean',
                'default'   => false,
                'attribute' => true
            ],
            'text' => [],
            'value' => [
                'attribute' => true
            ],
        ], ...$extend);
    }

    public function element()
    {
        return new Element($this->tag(), $this->text(), $this->attributes());
    }

}
