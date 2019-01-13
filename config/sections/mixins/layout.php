<?php

return [
    'props' => [
        /**
         * Section layout. Available layout methods: list, cards.
         */
        'layout' => function (string $layout = 'list') {
            return $layout === 'cards' ? 'cards' : 'list';
        }
    ]
];
