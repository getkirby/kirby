<?php

use Kirby\Cms\Find;

return function ($kirby) {
    return [
        'breadcrumbLabel' => function () use ($kirby) {
            return $kirby->site()->title()->or(t('view.site'))->toString();
        },
        'icon'   => 'home',
        'label'  => t('view.site'),
        'menu'   => true,
        'routes' => [
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

        ]
    ];
};
