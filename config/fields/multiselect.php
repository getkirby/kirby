<?php

return [
    'mixins' => ['options', 'tags'],
    'props' => [
        'search' => function (bool $search = true) {
            return $search;
        },
        'sort' => function (bool $sort = false) {
            return $sort;
        }
    ]
];
