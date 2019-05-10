<?php

return [
    'props' => [
        /**
         * Unset inherited props
         */
        'placeholder' => null,

        /**
         * Sets the default time when a new page/file/user is created
         */
        'default' => function ($default = null) {
            return $default;
        },
        /**
         * Changes the clock icon
         */
        'icon' => function (string $icon = 'clock') {
            return $icon;
        },
        /**
         * `12` or `24` hour notation. If `12`, an AM/PM selector will be shown.
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
            return $value;
        }
    ],
    'computed' => [
        'default' => function () {
            return $this->toTime($this->default);
        },
        'format' => function () {
            return $this->notation === 24 ? 'H:i' : 'h:i a';
        },
        'value' => function () {
            return $this->toTime($this->value);
        }
    ],
    'methods' => [
        'toTime' => function ($value) {
            if ($timestamp = timestamp($value, $this->step)) {
                return date('H:i', $timestamp);
            }

            return null;
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
