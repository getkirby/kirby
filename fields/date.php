<?php

return [
    'input' => function ($model, $field, $value, $options) {
        $format = $options['format'] ?? 'Y-m-d';
        return date($format, strtotime($value));
    },
    'output' => function ($model, $key, $value, $options) {

        if ($value !== null) {
            $date = strtotime($value);

            return date(DATE_W3C, $date);
        }

    }
];
