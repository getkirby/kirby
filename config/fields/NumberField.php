<?php

return [
    'props' => [
        'after'       => null,
        'autofocus'   => false,
        'before'      => null,
        'icon'        => null,
        'min'         => null,
        'max'         => null,
        'step'        => 1,
        'required'    => false,
        'placeholder' => null
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
