<?php

use Kirby\Toolkit\Date;

return [
    'props' => [
        /**
         * Defines a custom format that is used when the field is saved
         */
        'format' => function (string $format = null) {
            return $format;
        }
    ],
    'methods' => [
        'toDatetime' => function ($value, string $format = 'Y-m-d H:i:s') {
            if ($date = Date::optional($value)) {
                if ($this->step) {
                    $step = Date::stepConfig($this->step);
                    $date->round($step['unit'], $step['size']);
                }

                return $date->format($format);
            }

            return null;
        }
    ],
    'save' => function ($value) {
        if ($date = Date::optional($value)) {
            return $date->format($this->format);
        }

        return '';
    },
];
