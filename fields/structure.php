<?php

use Kirby\Data\Handler\Yaml;

return [
    'input' => function ($model, $key, $value, $options) {
        return Yaml::encode($value);
    },
    'output' => function ($model, $key, $value, $options): array {

        if (is_string($value) === true) {
            return Yaml::decode($value);
        }

        if (is_array($value)) {
            return $value;
        }

        return [];

    },
    'validate' => function ($model, $key, $value, $options) {
        return true;
    },
];
