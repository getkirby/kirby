<?php

return [
    'props' => [
        /**
         * Enable/disable the search in the sections
         */
        'search' => function (bool $search = false) {
            return $search;
        }
    ],
    'computed' => [
        'query' => function () {
            return get('query');
        }
    ]
];
