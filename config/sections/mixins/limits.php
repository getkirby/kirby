<?php

use Kirby\Toolkit\I18n;

return [
    'props' => [
        /**
         * Sets the minimum number of required entries in the section
         */
        'min' => function (int $min = null) {
            return $min;
        },
        /**
         * Sets the maximum number of allowed entries in the section
         */
        'max' => function (int $max = null) {
            return $max;
        }
    ],
    'computed' => [
        'errors' => function () {
            $errors = [];

            if ($this->validateMax() === false) {
                $errors['max'] = I18n::template('error.section.' . $this->type . '.max.' . I18n::form($this->max), [
                    'max'     => $this->max,
                    'section' => $this->headline
                ]);
            }

            if ($this->validateMin() === false) {
                $errors['min'] = I18n::template('error.section.' . $this->type . '.min.' . I18n::form($this->min), [
                    'min'     => $this->min,
                    'section' => $this->headline
                ]);
            }

            if (empty($errors) === true) {
                return [];
            }

            return [
                $this->name => [
                    'label'   => $this->headline,
                    'message' => $errors,
                ]
            ];
        },
    ],
    'methods' => [
        'isFull' => function () {
            if ($this->max) {
                return $this->total >= $this->max;
            }

            return false;
        },
        'validateMin' => function () {
            if ($this->min && $this->min > $this->total) {
                return false;
            }

            return true;
        },
        'validateMax' => function () {
            if ($this->max && $this->total > $this->max) {
                return false;
            }

            return true;
        }
    ]
];
