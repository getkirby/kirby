<?php

namespace Kirby\Form\Input;

use Kirby\Form\Input;

class Text extends Input
{

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
            'disabled' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'inputmode' => [
                'type'      => 'string',
                'attribute' => true
            ],
            'max' => [
                'attribute' => true
            ],
            'maxlength' => [
                'type'      => 'integer',
                'attribute' => true
            ],
            'min' => [
                'attribute' => true
            ],
            'minlength' => [
                'type'      => 'integer',
                'attribute' => true
            ],
            'name' => [
                'default'   => 'text',
                'attribute' => true
            ],
            'pattern' => [
                'type'      => 'string',
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
            'spellcheck' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'type' => [
                'type'      => 'string',
                'default'   => 'text',
                'attribute' => true
            ],
            'value' => [
                'type'      => 'string',
                'attribute' => true
            ]
        ], ...$extend);
    }

}
