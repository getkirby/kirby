<?php

return [
    'extends' => 'number',
    'props' => [
        'max' => function (float $max = 100) {
            return $max;
        },
        'tooltip' => function ($tooltip = true) {
            return $tooltip;
        },
    ]
];
