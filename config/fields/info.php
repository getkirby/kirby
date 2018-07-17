<?php

use Kirby\Toolkit\I18n;

return [
    'save' => false,
    'props' => [
        'text' => function ($value = null) {

            $text = I18n::translate($value, $value);
            $text = $this->model()->toString($text);

            return kirbytext($text);
        },
    ],
];
