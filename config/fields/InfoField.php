<?php

use Kirby\Util\I18n;

return [
    'props' => [
        'text' => function ($value = null) {
            return I18n::translate($value, $value);
        }
    ]
];
