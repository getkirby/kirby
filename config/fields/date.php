<?php

return [
    'props' => [
        'format' => function (string $format = DATE_W3C) {
            return $format;
        },
        'icon' => function (string $icon = "calendar") {
            return $icon;
        },
        'max' => function (string $max = null) {
            return $this->toDate($max);
        },
        'min' => function (string $min = null) {
            return $this->toDate($min);
        },
        'time' => function ($time = false) {
            return $time;
        },
        'value' => function ($value = null) {
            return $this->toDate($value);
        }
    ],
    'computed' => [
        'default' => function () {
            return $this->props['default'] ?? ($this->required() ? 'today' : null);
        }
    ],
    'methods' => [
        'toDate' => function ($value) {
            if ($date = strtotime($value)) {
                return date(DATE_W3C, $date);
            }
        },
        'toString' => function ($value): string {
            if ($date = strtotime($value)) {
                return date('Y-m-d H:i:s', $date);
            }

            return '';
        },
    ],
    'validations' => [
        'required',
        'date'
    ]
];
