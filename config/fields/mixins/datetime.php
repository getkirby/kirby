<?php

return [
    'methods' => [
        'toDatetime' => function ($value, string $format = 'Y-m-d H:i:s') {
            if ($timestamp = timestamp($value, $this->step)) {
                return date($format, $timestamp);
            }

            return null;
        },
        'toContent' => function ($value, string $format = 'Y-m-d H:i:s') {
            if ($value !== null && $timestamp = strtotime($value)) {
                return date($format, $timestamp);
            }

            return '';
        }
    ]
];
