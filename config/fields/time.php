<?php

return [
    'props' => [
        'default' => function (string $default = null) {
            return $this->toTime($default);
        },
        'icon' => function (string $icon = 'clock') {
            return $icon;
        },
        'notation' => function (int $value = 24) {
            return $value === 24 ? 24 : 12;
        },
        'step' => function (int $step = 5) {
            return $step;
        },
        'value' => function ($value = null) {
            return $this->toTime($value);
        }
    ],
    'computed' => [
        'format' => function () {
            return $this->notation === 24 ? 'H:i' : 'h:i a';
        }
    ],
    'methods' => [
        'toTime' => function ($value) {
            if ($timestamp = strtotime($value)) {
                return date('H:i', $timestamp);
            }
        }
    ],
    'toString' => function ($value): string {
        if ($timestamp = strtotime($value)) {
            return date($this->format, $timestamp);
        }

        return '';
    },
    'validations' => [
        'time',
    ]
];
