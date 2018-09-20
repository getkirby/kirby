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
        'options' => function (): array {
            return $this->getOptions();
        },
    ],
    'methods' => [
        'getOptions' => function () {
            return Options::factory(
                $this->options(),
                $this->props,
                $this->model()
            );
        },
        'sanitizeOptions' => function ($options) {
            $allowed = array_column($this->options(), 'value');
            return array_intersect($options, $allowed);
        }
    ]
];
