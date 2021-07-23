<?php

use Kirby\Cms\Find;

$files = require __DIR__ . '/../files/dropdowns.php';

return [
    'users/(:any)' => function (string $id) {
        return Find::user($id)->panel()->dropdown();
    },
    '(users/.*?)/files/(:any)' => $files['file'],
];
