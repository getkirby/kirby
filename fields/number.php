<?php

use Kirby\Util\Str;

return [
    'output' => function ($model, $key, $value, $options) {
        return $value != '' ? floatval($value) : null;
    }
];
