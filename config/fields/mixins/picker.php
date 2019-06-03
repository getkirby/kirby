<?php


return [
    'props' => [
        /**
         * The placeholder text if none have been selected yet
         */
        'empty' => function ($empty = null) {
            return I18n::translate($empty, $empty);
        },

        /**
         * The minimum number of required selected
         */
        'min' => function (int $min = null) {
            return $min;
        },

        /**
         * The maximum number of allowed selected
         */
        'max' => function (int $max = null) {
            return $max;
        },

        /**
         * If `false`, only a single one can be selected
         */
        'multiple' => function (bool $multiple = true) {
            return $multiple;
        }
    ],
];
