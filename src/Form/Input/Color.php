<?php

namespace Kirby\Form\Input;

use Kirby\Form\Input;

class Color extends Input
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'autofocus' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'disabled' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'name' => [
                'default' => 'color',
            ],
            'readonly' => [
                'type'      => 'boolean',
                'attribute' => true
            ],
            'type' => [
                'default'   => 'color',
                'attribute' => true
            ],
            'value' => [
                'type'      => 'string',
                'attribute' => true
            ]
        ], ...$extend);
    }

    public function validate($input): bool
    {
        return preg_match('!^\#[a-z0-9]{6}$!i', $input) !== 0;
    }

}
