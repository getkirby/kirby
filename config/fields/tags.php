<?php

return [
    'mixins' => ['options', 'tags'],
    'props' => [
        'accept' => function ($value = 'all') {
            return V::in($value, ['all', 'options']) ? $value : 'all';
        },
        'icon' => function ($icon = 'tag') {
            return $icon;
        }
    ]
];
