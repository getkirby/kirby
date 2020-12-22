<?php

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
            if ($timestamp = timestamp($value, $this->step)) {
                return date($format, $timestamp);
            }

            return null;
        }
    ],
    'save' => function ($value) {
        if ($value !== null && $timestamp = strtotime($value)) {
            return date($this->format, $timestamp);
        }

        return '';
    },
];
