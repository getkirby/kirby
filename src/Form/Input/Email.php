<?php

namespace Kirby\Form\Input;

use Kirby\Toolkit\V;

class Email extends Text
{

    public function schema(...$extend): array
    {
        return parent::schema([
            'autocomplete' => [
                'default' => 'email',
            ],
            'name' => [
                'default' => 'email'
            ],
            'type' => [
                'default' => 'email'
            ],
        ], ...$extend);
    }

    public function validate($input): bool
    {
        return V::email($input);
    }

}
