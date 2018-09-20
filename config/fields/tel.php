<?php

return [
    'extends' => 'text',
    'props' => [
        'autocomplete' => function (string $autocomplete = 'tel') {
            return $autocomplete;
        },
        'counter' => null,
        'icon' => function (string $icon = 'phone') {
            return $icon;
        }
    ]
];
