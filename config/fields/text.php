<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\Str;

return [
    'props' => [

        /**
         * The field value will be converted with the selected converter before the value gets saved. Available converters: `lower`, `upper`, `ucfirst`, `slug`
         */
        'converter' => function ($value = null) {
            if ($value !== null && in_array($value, array_keys($this->converters())) === false) {
                throw new InvalidArgumentException([
                    'key'  => 'field.converter.invalid',
                    'data' => ['converter' => $value]
                ]);
            }

            return $value;
        },

        /**
         * Shows or hides the character counter in the top right corner
         */
        'counter' => function (bool $counter = true) {
            return $counter;
        },

        /**
         * Maximum number of allowed characters
         */
        'maxlength' => function (int $maxlength = null) {
            return $maxlength;
        },

        /**
         * Minimum number of required characters
         */
        'minlength' => function (int $minlength = null) {
            return $minlength;
        },

        /**
         * A regular expression, which will be used to validate the input
         */
        'pattern' => function (string $pattern = null) {
            return $pattern;
        },

        /**
         * If `false`, spellcheck will be switched off
         */
        'spellcheck' => function (bool $spellcheck = false) {
            return $spellcheck;
        },
    ],
    'computed' => [
        'default' => function () {
            return $this->convert($this->default);
        },
        'value' => function () {
            return (string)$this->convert($this->value);
        }
    ],
    'methods' => [
        'convert' => function ($value) {
            if ($this->converter() === null) {
                return $value;
            }

            $converter = $this->converters()[$this->converter()];

            if (is_array($value) === true) {
                return array_map($converter, $value);
            }

            return call_user_func($converter, trim($value ?? ''));
        },
        'converters' => function (): array {
            return [
                'lower' => function ($value) {
                    return Str::lower($value);
                },
                'slug' => function ($value) {
                    return Str::slug($value);
                },
                'ucfirst' => function ($value) {
                    return Str::ucfirst($value);
                },
                'upper' => function ($value) {
                    return Str::upper($value);
                },
            ];
        },
    ],
    'validations' => [
        'minlength',
        'maxlength',
        'pattern'
    ]
];
