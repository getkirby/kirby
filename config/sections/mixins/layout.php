<?php

return [
    'props' => [
        /**
         * Section layout.
         * Available layout methods: `list`, `cardlets`, `cards`, `table`.
         */
        'layout' => function (string $layout = 'list') {
            $layouts = ['list', 'cardlets', 'cards', 'table'];
            return in_array($layout, $layouts) ? $layout : 'list';
        }
    ]
];
