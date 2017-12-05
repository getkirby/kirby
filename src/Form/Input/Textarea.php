<?php

namespace Kirby\Form\Input;

use Kirby\Form\Input;
use Kirby\Html\Element;

class Textarea extends Input
{

    public function tag(): string
    {
        return 'textarea';
    }

    public function schema(...$extend): array
    {
        return parent::schema([
            'autocomplete' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'autofocus' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'cols' => [
                'type'      => 'integer',
                'attribute' => true
            ],
            'disabled' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'maxlength' => [
                'type'      => 'integer',
                'attribute' => true
            ],
            'minlength' => [
                'type'      => 'integer',
                'attribute' => true
            ],
            'name' => [
                'default'   => 'textarea',
                'attribute' => true
            ],
            'placeholder' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'readonly' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'rows' => [
                'type'      => 'integer',
                'attribute' => true
            ],
            'spellcheck' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'value' => [
                'type' => 'string'
            ]
        ], ...$extend);
    }

    public function element()
    {
        return new Element($this->tag(), $this->value(), $this->attributes());
    }

}
