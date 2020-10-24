<?php

use Kirby\Exception\Exception;

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
         * Latest time, which can be selected/saved (H:i or H:i:s)
         */
        'max' => function (string $max = null) {
            return $max ? $this->toDatetime(date('Y-m-d ') . $max) : null;
        },
        /**
         * Earliest time, which can be selected/saved (H:i or H:i:s)
         */
        'min' => function (string $min = null) {
            return $min ? $this->toDatetime(date('Y-m-d ') . $min) : null;
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
        'minMax' => function ($value) {
            $min    = $this->min ? strtotime($this->min) : null;
            $max    = $this->max ? strtotime($this->max) : null;
            $value  = strtotime($this->value());
            $format = 'H:i:s';
            $errors = [];

            if ($value && $min && $value < $min) {
                $errors['min'] = $min;
            }

            if ($value && $max && $value > $max) {
                $errors['max'] = $max;
            }

            if (empty($errors) === false) {
                if ($min && $max) {
                    throw new Exception([
                        'key' => 'validation.time.between',
                        'data' => [
                            'min' => date($format, $min),
                            'max' => date($format, $max)
                        ]
                    ]);
                } elseif ($min) {
                    throw new Exception([
                        'key' => 'validation.time.after',
                        'data' => [
                            'time' => date($format, $min),
                        ]
                    ]);
                } else {
                    throw new Exception([
                        'key' => 'validation.time.before',
                        'data' => [
                            'time' => date($format, $max),
                        ]
                    ]);
                }
            }

            return true;
        },
    ]
];
