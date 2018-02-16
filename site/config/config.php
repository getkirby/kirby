<?php

return [
    'license' => 'K3-alpha',
    'urls' => [
        'index' => 'https://getkirby.com',
        'media' => 'https://cdn.getkirby.com',
        'panel' => 'https://panel.getkirby.com',
        'api' => 'https://getkirby.com/api'
    ],
    'debug' => true,
    'pages' => [
        'home' => 'home',
        'error' => 'error'
    ],
    'content' => [
        'extension' => 'txt',
        'ignore' => function ($file) {

        }
    ],
    'cache' => [
        'enabled' => true,
        'driver' => 'memcached',
        'options' => [
            'host' => 'localhost:11211'
        ],
        'autoupdate' => false,
        'ignore' => function ($page) {

        }
    ],
    'date' => [
        'handler' => 'strftime'
    ],
    'headers' => [

    ],
    'kirbytext' => [

    ],
    'routes' => [

    ]
];
