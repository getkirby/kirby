<?php

return [
    'props' => [
        /**
         * Unset inherited props
         */
        'after'  => null,
        'before' => null,

        /**
         * Enables/disables the format buttons. Can either be true/false or a list of allowed buttons. Available buttons: headlines, italic, bold, link, email, list, code, ul, ol
         */
        'buttons' => function ($buttons = true) {
            return $buttons;
        },

        /**
         * Enables/disables the character counter in the top right corner
         */
        'counter' => function (bool $counter = true) {
            return $counter;
        },

        /**
         * Sets the default text when a new Page/File/User is created
         */
        'default' => function (string $default = null) {
            return trim($default);
        },

        /**
         * Maximum number of allowed characters
         */
        'maxlength' => function (int $maxlength = null) {
            return $maxlength;
        },

        /**
         * Minimum number of required characters
         */
        'minlength' => function (int $minlength = null) {
            return $minlength;
        },

        /**
         * Changes the size of the textarea. Available sizes: small, medium, large, huge
         */
        'size' => function (string $size = null) {
            return $size;
        },

        'value' => function (string $value = null) {
            return trim($value);
        }
    ],
    'validations' => [
        'minlength',
        'maxlength'
    ]
];
