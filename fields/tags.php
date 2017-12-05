<?php

use Kirby\Util\Str;

return [
    'props' => function ($props) {
        return [
            'data' => ['awesome', 'nice']
        ];
    },
    'value' => function ($value): array {

        if (is_string($value) === true) {
            return Str::split($value, ',');
        }

        if (is_array($value)) {
            return $value;
        }

        return [];

    },
    'result' => function ($input) {
        return implode(', ', (array)$input);
    },
    'validate' => function ($input) {
        return false;
    },
];
