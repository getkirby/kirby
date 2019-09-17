<?php

use Kirby\Exception\Exception;

return [
    'props' => [
        /**
         * Default date when a new page/file/user gets created
         */
        'default' => function ($default = null) {
            return $default;
        },

        /**
         * Defines a custom format that is used when the field is saved
         */
        'format' => function (string $format = null) {
            return $format;
        },

        /**
         * Changes the calendar icon to something custom
         */
        'icon' => function (string $icon = 'calendar') {
            return $icon;
        },
        /**
         * Youngest date, which can be selected/saved
         */
        'max' => function (string $max = null) {
            return $this->toDate($max);
        },
        /**
         * Oldest date, which can be selected/saved
         */
        'min' => function (string $min = null) {
            return $this->toDate($min);
        },
        /**
         * The placeholder is not available
         */
        'placeholder' => null,
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
            return $this->toDate($this->default);
        },
        'format' => function () {
            return $this->props['format'] ?? ($this->time() === false ? 'Y-m-d' : 'Y-m-d H:i');
        },
        'value' => function () {
            return $this->toDate($this->value);
        },
    ],
    'methods' => [
        'toDate' => function ($value) {
            if ($timestamp = timestamp($value, $this->time['step'] ?? 5)) {
                return date(DATE_W3C, $timestamp);
            }

            return null;
        }
    ],
    'save' => function ($value) {
        if ($value !== null && $date = strtotime($value)) {
            return date($this->format(), $date);
        }

        return '';
    },
    'validations' => [
        'date',
        'minMax' => function ($value) {
            $min    = $this->min ? strtotime($this->min) : null;
            $max    = $this->max ? strtotime($this->max) : null;
            $value  = strtotime($this->value());
            $format = 'd.m.Y';
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
