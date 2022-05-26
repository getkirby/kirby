<?php

return [
    'props' => [
        /**
         * Enable/disable the search in the sections
         */
        'search' => function (bool $search = false): bool {
            return $search;
        }
    ],
    'computed' => [
        'query' => function (): ?string {
            return get('query');
        }
    ]
];
