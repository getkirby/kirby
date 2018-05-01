<?php

return array_replace_recursive(require __DIR__ . '/text.php', [
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
        'required',
        'minlength',
        'maxlength',
        'url'
    ],
]);
