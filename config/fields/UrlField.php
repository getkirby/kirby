<?php

use Kirby\Util\I18n;

return array_replace_recursive(require 'TextField.php', [
    'props' => [
        'autocomplete' => 'url',
        'icon'         => 'url',
        'placeholder'  => function ($value = null) {
            return I18n::translate($value, $value) ?? 'https://example.com';
        },
    ],
    'methods' => [
        'validate' => function () {
            $this->validate('required');
            $this->validate('length');
            $this->validate('url');
        }
    ]
]);
