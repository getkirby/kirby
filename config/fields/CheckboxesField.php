<?php

return [
    'props' => [
        'options'   => null,
        'required'  => false
    ],
    'methods' => [
        'toApi' => function ($value): array {
            return $this->valueFromList($value, ', ');
        },
        'toString' => function ($value): string {
            return $this->valueToList($value, ', ');
        },
        'validate' => function () {
            $this->validate('required');
            $this->validate('multipleOptions');
        }
    ]
];
