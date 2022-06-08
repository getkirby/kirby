<?php

return [
    'mixins' => ['options'],
    'props' => [
        /**
         * Unset inherited props
         */
        'after'       => null,
        'before'      => null,
        'icon'        => null,
        'placeholder' => null,

        'grow' => function (bool $grow = true) {
            return $grow;
        },
        'labels' => function (bool $labels = true) {
            return $labels;
        },
        'reset' => function (bool $reset = true) {
            return $reset;
        }
    ],
    'computed' => [
        'default' => function () {
            return $this->sanitizeOption($this->default);
        },
        'value' => function () {
            return $this->sanitizeOption($this->value) ?? '';
        },
    ]
];
