<?php

return [
    'value' => function ($value) {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    },
    'result' => function ($input) {
        return $input === true ? 'true' : 'false';
    },
];
