<?php

use Kirby\Cms\Find;

$files = require __DIR__ . '/../files/dropdowns.php';

return [
    'pages/(:any)' => function (string $path) {
        return Find::page($path)->panel()->dropdown([
            'view'   => get('view'),
            'sort'   => get('sort'),
            'delete' => get('delete')
        ]);
    },
    '(site|pages/.*?)/files/(:any)' => $files['file']
];
