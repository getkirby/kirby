<?php

return [
    'type'   => 'datetime',
    'mixins' => 'date',
    'props'  => [
        '12h' => [
            'default' => false,
            'type'    => 'boolean',
        ],
        'icon' => [
            'default' => 'calendar',
        ],
        'label' => [
            'default' => 'Date'
        ],
        'name' => [
            'default' => 'date'
        ],
        'step' => [
            'default' => 60,
            'type'    => 'integer'
        ],
        'value' => [
            'type' => 'string'
        ]
    ],
];
