<?php

use Kirby\Util\A;

return [
    'props' => [
        'options' => [
            'required' => true,
            'default'  => []
        ],
        'query' => [
            'type' => 'array'
        ],
        'value' => [
            'type' => 'scalar',
        ]
    ],
    'computed' => [
        'options' => function () {

            if (is_array($this->_props->options) === true) {
                return $this->_props->options;
            }

            switch ($this->_props->options) {
                case 'query':
                    return [
                        []
                    ];
                case 'url':
            }

            throw new Exception('Invalid options');

        }
    ],
    'methods' => [
        'values' => function () {
            return A::pluck($this->options, 'value');
        },
        'validate' => function ($value) {
            return in_array($value, $this->values(), true) === true;
        }
    ]
];
