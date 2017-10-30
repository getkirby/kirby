<?php

return [
    'input' => function ($model, $field, $value, $options) {
        $format = $options['format'] ?? 'Y-m-d';
        return date($format, strtotime($value));
    }
];
