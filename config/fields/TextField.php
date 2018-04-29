<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Util\I18n;
use Kirby\Util\Str;

return [
    'props' => [
        'after'       => null,
        'before'      => null,
        'converter'   => function ($value = null) {
            if ($value !== null && in_array($value, array_keys($this->converters())) === false) {
                throw new InvalidArgumentException([
                    'key'  => 'form.converter.invalid',
                    'data' => ['converter' => $value]
                ]);
            }

            return $value;
        },
        'counter'    => function ($value = null) {
            if ($this->maxLength() !== null || $this->minLength() !== null) {
                return true;
            }

            return $value ?? false;
        },
        'icon'        => null,
        'pattern'     => null,
        'placeholder' => function ($value = null) {
            return I18n::translate($value, $value);
        },
        'required'    => false,
        'spellcheck'  => false
    ],
    'methods' => [
        'convert' => function ($value): string {
            if ($this->converter() === null) {
                return $value;
            }

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
        'toString' => function ($value): string {
            return $this->convert(trim($value));
        },
        'validate' => function () {
            $this->validate('required');
            $this->validate('length');
        }
    ]
];
