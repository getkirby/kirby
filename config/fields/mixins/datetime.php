<?php

return [
    'methods' => [
        'toDatetime' => function ($value, string $format = 'Y-m-d H:i:s') {
            if ($timestamp = timestamp($value, $this->step)) {
                return $this->toISO($timestamp, $format);
            }

            return null;
        },
        'toISO' => function (int $time, string $format = 'Y-m-d H:i:s') {
            return date($format, $time);
        }
    ]
];
