<?php

return [
    'value' => function ($value) {
        return $value != '' ? floatval($value) : null;
    }
];
