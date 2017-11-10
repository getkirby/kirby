<?php

return [
    'input' => function ($model, $field, $value, $options) {
        $format = $options['format'] ?? 'd-m-Y H:i';
        return date($format, strtotime($value));

    },
    'output' => function ($model, $key, $value, $options) {

        if ($value !== null) {
            $datetime = strtotime($value);

            return [
                'date' => date(DATE_W3C, $datetime),
                'time' => [
                    'hour'   => date('H', $datetime),
                    'minute' => date('i', $datetime)
                ]
            ];
        }

    }
];
