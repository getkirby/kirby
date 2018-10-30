<?php

return [
    'extends' => 'radio',
    'props' => [
        /**
         * Custom icon to replace the arrow down.
         */
        'icon' => function (string $icon = null) {
            return $icon;
        },
    ]
];
