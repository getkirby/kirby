<?php

return [
    'props' => [
        /**
         * Changes the layout of the selected entries.
         * Available layouts: `list`, `cardlets`, `cards`, `table`
         */
        'layout' => function (string $layout = 'list') {
            $layouts = ['list', 'cardlets', 'cards', 'table'];
            return in_array($layout, $layouts) ? $layout : 'list';
        },

        /**
         * Layout size for cards: `tiny`, `small`, `medium`, `large` or `huge`
         */
        'size' => function (string $size = 'auto') {
            return $size;
        },
    ]
];
