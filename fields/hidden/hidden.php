<?php

return [
    'type'  => 'hidden',
    'props' => [
        'label' => [
            'value' => null
        ]
    ],
    'methods' => [
        'toArray' => function () {
            return [
                'name'  => $this->name,
                'value' => $this->value,
            ];
        }
    ]

];
