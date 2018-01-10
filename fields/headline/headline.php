<?php

return [
    'type'  => 'headline',
    'save'  => false,
    'props' => [
        'numbered' => [
            'type'    => 'boolean',
            'default' => true
        ]
    ],
    'methods' => [
        'toArray' => function () {
            return [
                'name'     => $this->name,
                'label'    => $this->label,
                'numbered' => $this->numbered,
            ];
        }
    ]

];
