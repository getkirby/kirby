<?php

return [
    'props' => [
        'options'  => null,
        'required' => false,
    ],
    'methods' => [
        'validate' => function () {
            $this->validate('required');
            $this->validate('singleOption');
        }
    ]
];
