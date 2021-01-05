<?php

use Kirby\Exception\Exception;
use Kirby\Toolkit\I18n;

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
         * Custom format (dayjs tokens: `HH`, `hh`, `mm`, `ss`, `a`) that is
         * used to display the field in the Panel
         */
        'display' => function ($display = null) {
            return I18n::translate($display, $display);
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
         * If `display` is defined, that option will take priority.
         */
        'notation' => function (int $value = 24) {
            return $value === 24 ? 24 : 12;
        },
        /**
         * Round to the nearest: sub-options for `unit` (minute) and `size` (5)
         */
        'step' => function ($step = null) {
            $default = [
                'size' => 5,
                'unit' => 'minute'
            ];

            if ($step === null) {
                return $default;
            }

            if (is_array($step) === true) {
                $step = array_merge($default, $step);
                $step['unit'] = strtolower($step['unit']);
                return $step;
            }

            if (is_int($step) === true) {
                return array_merge($default, ['size' => $step]);
            }

            if (is_string($step) === true) {
                return array_merge($default, ['unit' => strtolower($step)]);
            }
        },
        'value' => function ($value = null) {
            return $value;
        }
    ],
    'computed' => [
        'default' => function () {
            return $this->toDatetime($this->default, 'H:i:s');
        },
        'display' => function () {
            if ($this->display) {
                return $this->display;
            }

            return $this->notation === 24 ? 'HH:mm' : 'h:mm a';
        },
        'format' => function () {
            return $this->props['format'] ?? 'H:i:s';
        },
        'value' => function () {
            return $this->toDatetime($this->value, 'H:i:s');
        }
    ],
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
