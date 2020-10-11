<?php

return [
    'mixins' => ['datetime'],
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
         * Custom format (dayjs tokens) that is used to display
         * the field in the Panel
         */
        'display' => function (string $display = null) {
            return $display;
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
         * Round to the nearest: sub-options for `unit` (minute) and `size` (5)
         */
        'step' => function ($step = null) {
            if ($step === null) {
                return [
                    'size' => 5,
                    'unit' => 'minute'
                ];
            }

            if (is_array($step) === true) {
                return $step;
            }

            if (is_int($step) === true) {
                return [
                    'size' => $step,
                    'unit' => 'minute'
                ];
            }

            if (is_string($step) === true) {
                return [
                    'size' => 1,
                    'unit' => $step
                ];
            }

            throw new Exception('step option has to be defined as array');
        },
        'value' => function ($value = null) {
            return $value;
        }
    ],
    'computed' => [
        'default' => function () {
            return $this->toDatetime($this->default);
        },
        'display' => function () {
            if ($this->display) {
                return $this->display;
            }

            return $this->notation === 24 ? 'HH:mm' : 'hh:mm a';
        },
        'value' => function () {
            return $this->toDatetime($this->value);
        }
    ],
    'save' => function ($value): string {
        if ($value != null && $timestamp = strtotime($value)) {
            return date('H:i:s', $timestamp);
        }

        return '';
    },
    'validations' => [
        'time',
    ]
];
