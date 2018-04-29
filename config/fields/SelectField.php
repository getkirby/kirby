<?php

return [
    'props' => [
        'icon'     => 'angle-down',
        'options'  => null,
        'required' => false
    ],
    'methods' => [
        'validate' => function () {
            $this->validate('required');
            $this->validate('singleOption');
        }
    ]
];
