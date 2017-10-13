<?php

use Kirby\Toolkit\Str;

return [
    'read' => function ($model, $key, $value, $options): array {

        if (is_string($value) === true) {
            return Str::split($value, ',');
        }

        if (is_array($value)) {
            return $value;
        }

        return [];

    },
    'write' => function ($model, $key, $value, $options) {
        return implode(', ', (array)$value);
    },
    'validate' => function ($model, $key, $value, $options) {
        return true;
    },
];
