<?php

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
                'action'  => function (string $path) use ($kirby) {
                    return Panel::page($path)->panel()->route();
                }
            ],
            [
                'pattern' => 'pages/(:any)/files/(:any)',
                'action'  => function (string $id, string $filename) use ($kirby) {
                    return Panel::file('pages/' . $id, $filename)->panel()->route();
                }
            ],
            [
                'pattern' => 'site',
                'action'  => function () use ($kirby) {
                    return $kirby->site()->panel()->route();
                }
            ],
            [
                'pattern' => 'site/files/(:any)',
                'action'  => function (string $filename) use ($kirby) {
                    return Panel::file('site', $filename)->panel()->route();
                }
            ],

        ]
    ];
};
