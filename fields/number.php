<?php

use Kirby\Toolkit\Str;

return [
    'output' => function ($model, $key, $value, $options) {
        return $value != '' ? floatval($value) : null;
    }
];
