<?php

use Kirby\Toolkit\Str;
use Kirby\Data\Handler\Yaml;

return [
    'read' => function ($model, $key, $value, $options): array {

        if (is_string($value) === true) {
            return Yaml::decode($value);
        }

        if (is_array($value)) {
            return $value;
        }

        return [];

    },
    'write' => function ($model, $key, $value, $options) {
        return is_string($value) ? $value : Yaml::encode($value);
    },
    'validate' => function ($model, $key, $value, $options) {
        return true;
    },
];
