<?php

namespace Kirby\Form\Input;

use Kirby\Form\Input;

class Hidden extends Input
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'name' => [
                'default' => 'hidden',
            ],
            'type' => [
                'default'   => 'hidden',
                'attribute' => true
            ],
            'value' => [
                'attribute' => true
            ]
        ], ...$extend);
    }

}
