<?php

namespace Kirby\Form\Input;

class Number extends Text
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'name' => [
                'default' => 'number'
            ],
            'max' => [
                'type' => 'number'
            ],
            'min' => [
                'type' => 'number'
            ],
            'step' => [
                'default' => 'number'
            ],
            'type' => [
                'default' => 'number'
            ],
            'value' => [
                'type' => 'number'
            ]
        ], ...$extend);
    }

}
