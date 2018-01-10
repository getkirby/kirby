<?php

return [
    'mixins' => ['placeholder', 'length'],
    'type'   => 'text',
    'props'  => [
        'autocomplete' => [
            'type' => 'string'
        ],
        'label' => [
            'default' => 'Text'
        ],
        'name' => [
            'default' => 'text'
        ]
    ]
];
