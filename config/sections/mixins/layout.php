<?php

return [
    'props' => [
        'layout' => function (string $layout = 'list') {
            return $layout === 'cards' ? 'cards' : 'list';
        }
    ]
];
