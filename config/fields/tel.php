<?php

return array_replace_recursive(require __DIR__ . '/text.php', [
    'props' => [
        'autocomplete' => function (string $autocomplete = 'tel') {
            return $autocomplete;
        },
        'counter' => null,
        'icon' => function (string $icon = 'phone') {
            return $icon;
        }
    ]
]);
