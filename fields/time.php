<?php

return [
    'input' => function ($model, $field, $value, $options) {
        $format = $options['format'] ?? 'H:i';
        return date($format, strtotime($value));

    },
    'output' => function ($model, $key, $value, $options) {

        if ($value !== null) {
            $time = strtotime($value);

            return [
                'hour'   => date('H', $time),
                'minute' => date('i', $time)
            ];
        }

    }
];
