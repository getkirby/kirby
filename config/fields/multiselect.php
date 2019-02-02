<?php

return [
    'extends' => 'tags',
    'props' => [

        /**
         * Unset inherited props
         */
        'accept' => null,

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
         * If true, entries will be sorted alphabetically on selection
         */
        'sort' => function (bool $sort = false) {
            return $sort;
        },
    ]
];
