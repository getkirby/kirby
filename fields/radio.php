<?php

use Kirby\Util\A;

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
    'validate' => function ($input) {
        $options = A::pluck($this->prop('options'), 'value');
        return in_array($input, $options) === true;
    }
];
