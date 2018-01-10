<?php

use Kirby\Util\Str;

return [
    'type'  => 'tags',
    'props' => [
        'label' => [
            'default' => 'Tags'
        ],
        'lowercase' => [
            'default' => false,
            'type'    => 'boolean'
        ],
        'name' => [
            'default' => 'tags'
        ],
        'separator' => [
            'default' => ',',
            'type'    => 'string'
        ],
        'value' => [
            'type' => 'array',
        ]
    ],
    'methods' => [
        'createDataValue' => function ($value) {
            if (is_array($value) === false) {
                $value = Str::split($value, $this->separator);
            }

            if ($this->lowercase === true) {
                return array_map(Str::class . '::lower', $value);
            }

            return $value;
        },
        'createTextValue' => function (array $value) {
            $value = implode($this->separator . ' ', $value);

            if ($this->lowercase === true) {
                $value = Str::lower($value);
            }

            return $value;
        }
    ],
];
