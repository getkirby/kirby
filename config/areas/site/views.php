<?php

use Kirby\Cms\Find;

return [
    [
        'pattern' => 'pages/(:any)',
        'action'  => function (string $path) {
            return Find::page($path)->panel()->route();
        }
    ],
    [
        'pattern' => 'pages/(:any)/files/(:any)',
        'action'  => function (string $id, string $filename) {
            return Find::file('pages/' . $id, $filename)->panel()->route();
        }
    ],
    [
        'pattern' => 'site',
        'action'  => function () {
            return site()->panel()->route();
        }
    ],
    [
        'pattern' => 'site/files/(:any)',
        'action'  => function (string $filename) {
            return Find::file('site', $filename)->panel()->route();
        }
    ],
];
