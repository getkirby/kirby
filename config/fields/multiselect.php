<?php

return [
    'extends' => 'tags',
    'props' => [
        /**
         * Unset inherited props
         */
        'accept' => null,
        /**
         * Limit displayed items
         */
        'display' => function (int $display = null) {
            return $display;
        },
        /**
         * Custom icon to replace the arrow down.
         */
        'icon' => function (string $icon = null) {
            return $icon;
        },
        /**
         * Set minimum number of characters to search
         */
        'minSearch' => function (int $minSearch = 0) {
            return $minSearch;
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
