<?php

use Kirby\Util\A;
use Kirby\Util\Str;

return [
    'props' => function ($props) {

        if (empty($props['options']) === true) {
            return ['options' => []];
        }

        if (is_string($props['options']) === true) {
            return ['options' => $props['options']];
        }

        $options = [];

        foreach ($props['options'] as $value => $text) {
            $options[] = [
                'value' => $value,
                'text'  => $text
            ];
        }

        return [
            'options' => $options
        ];

    },
    'value' => function ($value) {

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
    'validate' => function (array $input) {

        $options = A::pluck($this->prop('options'), 'value');

        foreach ($input as $value) {
            if (in_array($value, $options) === false) {
                return false;
            }
        }

        return true;

    }
];
