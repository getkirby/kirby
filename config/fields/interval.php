<?php

use Kirby\Exception\Exception;
use Kirby\Data\Yaml;
use Kirby\Toolkit\V;

return [
    'props' => [
        /**
         * Default dates when a new page/file/user gets created
         */
        'default' => function ($default = null) {
            return $default;
        },

        /**
         * Defines the format that is used when the field
         * is displayed in the Panel
         */
        'display' => function (string $display = 'YYYY-MM-DD') {
            return $display;
        },

        /**
         * Defines a custom format that is used when the field is saved
         */
        'format' => function (string $format = 'Y-m-d') {
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
         * Must be an array of two parseable date strings
         */
        'value' => function ($value = null) {
            return $value;
        },
    ],
    'computed' => [
        'default' => function () {
            return $this->toDates($this->default);
        },
        'value' => function () {
            return $this->toDates($this->value);
        },
    ],
    'methods' => [
        'toDate' => function ($value) {
            if ($timestamp = timestamp($value)) {
                return date('Y-m-d', $timestamp);
            }

            return null;
        },
        'toDates' => function ($value) {
            if ($value === null) {
                return null;
            }

            return array_map(function ($date) {
                return $this->toDate($date);
            }, Yaml::decode($value));
        }
    ],
    'save' => function ($value) {
        if (is_array($value) === true) {
            $start = strtotime($value[0]);
            $end   = strtotime($value[1]);

            return [
                date($this->format(), $start),
                date($this->format(), $end)
            ];
        }

        return '';
    },
    'validations' => [
        'date' => function ($value) {
            if ($this->isEmpty($value) === false) {
                foreach ($value as $date) {
                    if (V::date($date) !== true) {
                        throw new InvalidArgumentException(
                            V::message('date', $date)
                        );
                    }
                }
            }

            return true;
        },
        'minMax' => function ($value) {
            $min    = $this->min ? strtotime($this->min) : null;
            $max    = $this->max ? strtotime($this->max) : null;
            $value  = $this->value();
            $start  = strtotime($value[0]);
            $end    = strtotime($value[1]);
            $format = 'd.m.Y';
            $errors = [];

            if ($value && $min && $start < $min) {
                $errors['min'] = $min;
            }

            if ($value && $max && $end > $max) {
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
