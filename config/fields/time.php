<?php

return [
    'props' => [
        /**
         * Unset inherited props
         */
        'placeholder' => null,

        /**
         * Sets the default time when a new Page/File/User is created
         */
        'default' => function (string $default = null) {
            return $this->toTime($default);
        },
        /**
         * Changes the clock icon
         */
        'icon' => function (string $icon = 'clock') {
            return $icon;
        },
        /**
         * 12 or 24 hour notation. If 12, an AM/PM selector will be shown.
         */
        'notation' => function (int $value = 24) {
            return $value === 24 ? 24 : 12;
        },
        /**
         * The interval between minutes in the minutes select dropdown.
         */
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
    'save' => function ($value): string {
        if ($timestamp = strtotime($value)) {
            return date($this->format, $timestamp);
        }

        return '';
    },
    'validations' => [
        'time',
    ]
];
