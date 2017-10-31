<?php

use Kirby\Toolkit\Str;

return [
    'setup' => function ($model, $params): array {

        if (is_string($params['options']) === true) {
            return ['options' => $params['options']];
        }

        $options = [];

        foreach ($params['options'] as $value => $text) {
            $options[] = [
                'value' => $value,
                'text'  => $text
            ];
        }

        return [
            'options' => $options
        ];

    },
    'input' => function ($model, $field, $value) {
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
];
