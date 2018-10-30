<?php

return [
    'extends' => 'number',
    'props' => [
        /**
         * Unset inherited props
         */
        'placeholder' => null,

        /**
         * The maximum value on the slider
         */
        'max' => function (float $max = 100) {
            return $max;
        },
        /**
         * Enables/disables the tooltip and set the before and after values
         */
        'tooltip' => function ($tooltip = true) {
            return $tooltip;
        },
    ]
];
