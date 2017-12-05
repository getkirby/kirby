<?php

namespace Kirby\Form\Input;

class Password extends Text
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'autocomplete' => [
                'default' => 'new-password',
            ],
            'name' => [
                'default' => 'password'
            ],
            'type' => [
                'default' => 'password'
            ],
        ], ...$extend);
    }

}
