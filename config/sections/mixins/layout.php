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
        },
        /**
         * The size option controls the size of cards. By default cards are auto-sized and the cards grid will always fill the full width. With a size you can disable auto-sizing. Available sizes: `tiny`, `small`, `medium`, `large`, `huge`
         */
        'size' => function (string $size = 'auto') {
            return $size;
        },
    ]
];
