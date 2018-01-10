<?php

use Kirby\Toolkit\V;

return [
    'props'   => [
        'maxLength' => [
            'type' => 'integer'
        ],
    ],
    'methods' => [
        'isTooLong' => function ($value) {
            if ($this->maxLength) {
                return v::maxLength($value, $this->maxLength) === false;
            }

            return false;
        }
    ]
];
