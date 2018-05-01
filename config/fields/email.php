<?php

return array_replace_recursive(require __DIR__ . '/text.php', [
    'props' => [
        'autocomplete' => function (string $autocomplete = 'email') {
            return $autocomplete;
        },
        'counter' => null,
        'icon' => function (string $icon = 'email') {
            return $icon;
        },
        'placeholder'  => function ($value = null) {
            return I18n::translate($value, $value) ?? 'mail@example.com';
        }
    ],
    'validations' => [
        'required',
        'minlength',
        'maxlength',
        'email'
    ]
]);
