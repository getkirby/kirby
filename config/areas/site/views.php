<?php

use Kirby\Cms\Find;

return [
    'page' => [
        'pattern' => 'pages/(:any)',
        'action'  => function (string $path) {
            return Find::page($path)->panel()->view();
        }
    ],
    'pageFile' => [
        'pattern' => 'pages/(:any)/files/(:any)',
        'action'  => function (string $id, string $filename) {
            return Find::file('pages/' . $id, $filename)->panel()->view();
        }
    ],
    'site' => [
        'pattern' => 'site',
        'action'  => function () {
            return site()->panel()->view();
        }
    ],
    'siteFile' => [
        'pattern' => 'site/files/(:any)',
        'action'  => function (string $filename) {
            return Find::file('site', $filename)->panel()->view();
        }
    ],
];
