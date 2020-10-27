<?php

use Kirby\Exception\Exception;
use Kirby\Toolkit\Str;

return [
    'mixins' => ['datetime'],
    'props' => [
        /**
         * Unset inherited props
         */
        'placeholder' => null,

        /**
         * Activate/deactivate the dropdown calendar
         */
        'calendar' => function (bool $calendar = true) {
            return $calendar;
        },


        /**
         * Default date when a new page/file/user gets created
         */
        'default' => function (string $default = null) {
            return $default;
        },

        /**
         * Custom format (dayjs tokens) that is used to display
         * the field in the Panel
         */
        'display' => function (string $display = 'YYYY-MM-DD') {
            return $display;
        },

        /**
         * Changes the calendar icon to something custom
         */
        'icon' => function (string $icon = 'calendar') {
            return $icon;
        },

        /**
         * Latest date, which can be selected/saved (Y-m-d)
         */
        'max' => function (string $max = null) {
            return $this->toDatetime($max);
        },
        /**
         * Earliest date, which can be selected/saved (Y-m-d)
         */
        'min' => function (string $min = null) {
            return $this->toDatetime($min);
        },

        /**
         * Round to the nearest: sub-options for `unit` (day) and `size` (1)
         */
        'step' => function ($step = null) {
            if ($step === null) {
                return [
                    'size' => 1,
                    'unit' => 'day'
                ];
            }

            if (is_array($step) === true) {
                return $step;
            }

            if (is_int($step) === true) {
                return [
                    'size' => $step,
                    'unit' => 'day'
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

        /**
         * Pass `true` or an array of time field options to show the time selector.
         */
        'time' => function ($time = false) {
            return $time;
        },
        /**
         * Must be a parseable date string
         */
        'value' => function ($value = null) {
            return $value;
        },
    ],
    'computed' => [
        'default' => function () {
            return $this->toDatetime($this->default);
        },
        'display' => function () {
            return Str::upper($this->display);
        },
        'step' => function () {
            if ($this->time !== false) {
                $timeField = require __DIR__ . '/time.php';
                return $timeField['props']['step']($this->time['step'] ?? null);
            }

            return $this->step;
        },
        'value' => function () {
            return $this->toDatetime($this->value);
        },
    ],
    'save' => function ($value) {
        if ($value !== null && $timestamp = timestamp($value)) {
            return $this->toISO($timestamp);
        }

        return '';
    },
    'validations' => [
        'date',
        'minMax' => function ($value) {
            $min    = $this->min ? strtotime($this->min) : null;
            $max    = $this->max ? strtotime($this->max) : null;
            $value  = strtotime($this->value());
            $format = $this->time === false ? 'd.m.Y' : 'd.m.Y H:i';
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
                        'key' => 'validation.date.between',
                        'data' => [
                            'min' => date($format, $min),
                            'max' => date($format, $max)
                        ]
                    ]);
                } elseif ($min) {
                    throw new Exception([
                        'key' => 'validation.date.after',
                        'data' => [
                            'date' => date($format, $min),
                        ]
                    ]);
                } else {
                    throw new Exception([
                        'key' => 'validation.date.before',
                        'data' => [
                            'date' => date($format, $max),
                        ]
                    ]);
                }
            }

            return true;
        },
    ]
];
