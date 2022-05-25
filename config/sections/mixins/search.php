<?php

return [
    'props' => [
        /**
         * Enable/disable the search in the sections
         */
        'search' => function (bool $search = false) {
            return $search;
        },
        /**
         * Sets the default query for the sections
         */
        'query' => function (string $query = null) {
            return get('query', $query);
        }
    ]
];
