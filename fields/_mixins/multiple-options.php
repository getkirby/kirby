<?php

use Kirby\Util\A;
use Kirby\Util\Str;

return [
    'extends' => 'options',
    'props' => [
        'value' => [
            'type' => 'array'
        ]
    ],
    'methods' => [
        'createDataValue' => function ($value) {
            if (is_array($value) === true) {
                return $value;
            }

            return Str::split($value, ',');
        },
        'createTextValue' => function ($value) {
            return implode(', ', $value);
        },
        'validate' => function ($value) {
            $values = $this->values();
            $result = true;

            foreach ($value as $key => $val) {
                if (in_array($val, $values, true) === false) {
                    $result = false;
                }
            }

            return $result;
        }
    ]
];
