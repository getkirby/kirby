<?php

use Kirby\Util\I18n;

return [
    'props' => [
        'icon'     => null,
        'required' => false,
        'text'     => function ($value = null) {
            $text = (string)I18n::translate($value, $value);
            return markdown(kirbytext($text));
        }
    ],
    'methods' => [
        'toString' => function ($value): string {
            return $value === true ? 'true' : 'false';
        },
        'toApi' => function ($value): bool {
            return in_array($value, [true, 'true', 1, '1', 'on'], true) === true;
        },
        'validate' => function () {
            $this->validate('required');
            $this->validate('boolean');
        }
    ]
];
