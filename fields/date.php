<?php

return [
    'input' => function ($model, $field, $value, $options) {
        $format = $options['format'] ?? 'd-m-Y';
        return date($format, strtotime($value));
    },
    'output' => function ($model, $key, $value, $options) {

        if ($value !== null) {
            $date = strtotime($value);

            return date(DATE_W3C, $date);
        }

    }
];
