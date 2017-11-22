<?php

use Kirby\Util\Str;

return [
    'input' => function ($model, $key, $value, $options) {
        return implode(', ', (array)$value);
    },
    'output' => function ($model, $key, $value, $options): array {

        if (is_string($value) === true) {
            return Str::split($value, ',');
        }

        if (is_array($value)) {
            return $value;
        }

        return [];

    },
    'validate' => function ($model, $key, $value, $options) {
        return true;
    },
];
