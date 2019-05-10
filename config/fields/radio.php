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

        /**
         * Arranges the radio buttons in the given number of columns
         */
        'columns' => function (int $columns = 1) {
            return $columns;
        },
    ],
    'computed' => [
        'default' => function () {
            return $this->sanitizeOption($this->default);
        },
        'value' => function () {
            return $this->sanitizeOption($this->value) ?? '';
        }
    ]
];
