<?php

return [
    'value' => function ($value) {
        if ($value !== null && $datetime = strtotime($value)) {
            return [
                'date' => date(DATE_W3C, $datetime),
                'time' => [
                    'hour'   => date('H', $datetime),
                    'minute' => date('i', $datetime)
                ]
            ];
        }

        return null;
    },
    'result' => function ($input) {
        $format = $this->props()['format'] ?? 'Y-m-d H:i';

        if ($date = strtotime($input)) {
            return date($format, $date);
        }

        return null;
    },
];
