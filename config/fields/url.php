<?php

return [
    'extends' => 'text',
    'props' => [
        'autocomplete' => function (string $autocomplete = 'url') {
            return $autocomplete;
        },
        'counter' => null,
        'icon' => function (string $icon = 'url') {
            return $icon;
        },
        'placeholder'  => function ($value = null) {
            return I18n::translate($value, $value) ?? 'https://example.com';
        }
    ],
    'validations' => [
        'minlength',
        'maxlength',
        'url'
    ],
];
