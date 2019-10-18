<?php

return [
    'extends' => 'tags',
    'props' => [

        /**
         * Unset inherited props
         */
        'accept' => null,

        /**
         * Hides the value of the options in parentheses.
         */
        'hideValue' => function (bool $hideValue = false) {
            return $hideValue;
        },

        /**
         * Custom icon to replace the arrow down.
         */
        'icon' => function (string $icon = null) {
            return $icon;
        },
        /**
         * Enable/disable the search in the dropdown
         */
        'search' => function (bool $search = true) {
            return $search;
        },
        /**
         * If `true`, selected entries will be sorted
         * according to their position in the dropdown
         */
        'sort' => function (bool $sort = false) {
            return $sort;
        },
    ]
];
