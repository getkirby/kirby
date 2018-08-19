<?php

use Kirby\Form\Options;

return [
    'props' => [
        'api' => function ($api = null) {
            return $api;
        },
        'options' => function ($options = []) {
            return $options;
        },
        'query' => function ($query = null) {
            return $query;
        },
    ],
    'computed' => [
        'options' => function () {
            return $this->getOptions();
        }
    ],
    'methods' => [
        'getOptions' => function () {
            return Options::factory(
                $this->props['options'],
                $this->props,
                $this->data['model'] ?? null
            );
        }
    ]
];
