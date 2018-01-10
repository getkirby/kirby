<?php

use Kirby\Toolkit\V;

return [
    'props'   => [
        'minLength' => [
            'type' => 'integer'
        ]
    ],
    'methods' => [
        'isTooShort' => function ($value) {
            if ($this->minLength) {
                return v::minLength($value, $this->minLength) === false;
            }

            return false;
        }
    ]
];
