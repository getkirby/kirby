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
    'methods' => [
        'getOptions' => function () {
            return Options::factory(
                $this->options(),
                $this->props,
                $this->model()
            );
        },
        'sanitizeOption' => function ($option) {
            $allowed = array_column($this->options(), 'value');
            return in_array($option, $allowed, true) === true ? $option : null;
        },
        'sanitizeOptions' => function ($options) {
            $allowed = array_column($this->options(), 'value');
            return array_intersect($options, $allowed);
        },
    ]
];
