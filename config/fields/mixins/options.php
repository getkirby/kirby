<?php

use Kirby\Form\Options;

return [
    'props' => [
        /**
         * API settings for options requests. This will only take affect when `options` is set to `api`.
         */
        'api' => function ($api = null) {
            return $api;
        },
        /**
         * An array with options
         */
        'options' => function ($options = []) {
            return $options;
        },
        /**
         * Query settings for options queries. This will only take affect when `options` is set to `query`.
         */
        'query' => function ($query = null) {
            return $query;
        },
    ],
    'computed' => [
        'options' => function (): array {
            return $this->getOptions();
        }
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
