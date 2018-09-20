<?php

return [
    'extends' => 'tags',
    'props' => [
        'icon' => function (string $icon = null) {
            return $icon;
        },
        'search' => function (bool $search = true) {
            return $search;
        },
        'sort' => function (bool $sort = false) {
            return $sort;
        },
    ]
];
