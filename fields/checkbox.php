<?php

return [
    'input' => function ($model, $field, $value) {
        return $value === true ? 'true' : 'false';
    },
    'output' => function ($model, $key, $value, $options): bool {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    },
];
