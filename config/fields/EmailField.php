<?php

use Kirby\Util\I18n;

return array_replace_recursive(require 'TextField.php', [
    'props' => [
        'autocomplete' => 'email',
        'icon'         => 'email',
        'placeholder'  => function ($value = null) {
            return I18n::translate($value, $value) ?? 'mail@example.com';
        }
    ],
    'methods' => [
        'validate' => function () {
            $this->validate('required');
            $this->validate('length');
            $this->validate('email');
        }
    ]
]);
