<?php

namespace Kirby\Form\Input;

class Tel extends Text
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'autocomplete' => [
                'default' => 'tel',
            ],
            'name' => [
                'default' => 'tel'
            ],
            'type' => [
                'default' => 'tel'
            ],
        ], ...$extend);
    }

}
