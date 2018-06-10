<?php

return [
    'mixins' => ['options'],
    'props' => [
        'value' => function ($value = null) {
            return Str::split($value, ', ');
        }
    ],
    'methods' => [
        'toString' => function ($value): string {
            return A::join($value, ', ');
        }
    ],
    'validations' => [
        'required',
        'options'
    ]
];
