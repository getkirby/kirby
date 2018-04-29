<?php

return [
    'props' => [
        'after'    => null,
        'before'   => null,
        'icon'     => null,
        'min'      => 0,
        'max'      => 100,
        'step'     => 1,
        'required' => false,
        'tooltip'  => true
    ],
    'methods' => [
        'toString' => function ($value) {
            return $this->isEmpty($value) === false ? floatval($value) : null;
        },
        'validate' => function () {
            $this->validate('required');
            $this->validate('minmax');
        }
    ]
];
