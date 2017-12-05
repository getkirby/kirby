<?php

namespace Kirby\Form\Input;

use Kirby\Toolkit\V;

class Url extends Text
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'autocomplete' => [
                'default' => 'url',
            ],
            'name' => [
                'default' => 'url'
            ],
            'type' => [
                'default' => 'url'
            ],
        ], ...$extend);
    }

    public function validate($input): bool
    {
        return V::url($input);
    }

}
