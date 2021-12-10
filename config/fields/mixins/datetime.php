<?php

use Kirby\Toolkit\Date;

return [
    'props' => [
        /**
         * Defines a custom format that is used when the field is saved
         */
        'format' => function (string $format = Date::W3C) {
            return $format;
        }
    ],
    'save' => function ($value) {
        if ($date = Date::optional($value)) {
            return $date->format($this->format);
        }

        return '';
    },
];
