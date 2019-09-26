<?php

use Kirby\Toolkit\I18n;

return [
    'props' => [
        /**
         * Text to be displayed
         */
        'text' => function ($value = null) {
            return I18n::translate($value, $value);
        },
    ],
    'computed' => [
        'text' => function () {
            if ($text = $this->text) {
                $text = $this->model()->toString($text);
                return kirbytext($text);
            }
        }
    ],
    'save' => false,
];
