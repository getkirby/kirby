<?php

return [
    'props' => [
        /**
         * Section layout.
         * Available layout methods: `list`, `cardlets`, `cards`.
         */
        'layout' => function (string $layout = 'list') {
            $layouts = ['list', 'cardlets', 'cards'];
            return in_array($layout, $layouts) ? $layout : 'list';
        }
    ]
];
