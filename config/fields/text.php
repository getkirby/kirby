<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;

return [
    'props' => [
        'converter' => function ($value = null) {
            if ($value !== null && in_array($value, array_keys($this->converters())) === false) {
                throw new InvalidArgumentException([
                    'key'  => 'form.converter.invalid',
                    'data' => ['converter' => $value]
                ]);
            }

            return $value;
        },
        'counter' => function (bool $counter = true) {
            return $counter;
        },
        'maxlength' => function (int $maxlength = null) {
            return $maxlength;
        },
        'minlength' => function (int $minlength = null) {
            return $minlength;
        },
        'pattern' => function (string $pattern = null) {
            return $pattern;
        },
        'spellcheck' => function (bool $spellcheck = false) {
            return $spellcheck;
        },
    ],
    'computed' => [
        'default' => function () {
            return $this->convert($this->default);
        },
        'value' => function () {
            return $this->convert($this->value);
        }
    ],
    'methods' => [
        'convert' => function ($value) {
            if ($this->converter() === null) {
                return $value;
            }

            $value     = trim($value);
            $converter = $this->converters()[$this->converter()];

            if (is_array($value) === true) {
                return array_map($converter, $value);
            }

            return call_user_func($converter, $value);
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
        'maxlength'
    ]
];
