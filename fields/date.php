<?php

return [
    'value' => function ($value) {
        if ($value !== null && $date = strtotime($value)) {
            return date(DATE_W3C, $date);
        }
        return null;
    },
    'result' => function ($input) {
        $format = $this->props()['format'] ?? 'Y-m-d';

        if ($date = strtotime($input)) {
            return date($format, $date);
        }

        return null;
    },
    'validate' => function ($input) {

    }
];
