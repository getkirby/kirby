<?php

return [
    'methods' => [
        'toDatetime' => function ($value) {
            if ($timestamp = timestamp($value, $this->step)) {
                return $this->toISO($timestamp);
            }

            return null;
        },
        'toISO' => function (int $time) {
            return date('Y-m-d H:i:s', $time);
        }
    ]
];
