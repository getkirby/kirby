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
                    if (!$page = $kirby->page(str_replace('+', '/', $path))) {
                        return t('error.page.undefined');
                    }

                    return $page->panel()->route();
                }
            ],
            [
                'pattern' => 'pages/(:any)/files/(:any)',
                'action'  => function (string $id, string $filename) use ($kirby) {
                    $id       = str_replace('+', '/', $id);
                    $filename = urldecode($filename);

                    if (!$page = $kirby->page($id)) {
                        return t('error.page.undefined');
                    }

                    if (!$file = $page->file($filename)) {
                        return t('error.file.undefined');
                    }

                    return $file->panel()->route();
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
                    $filename = urldecode($filename);

                    if (!$file = $kirby->site()->file($filename)) {
                        return t('error.file.undefined');
                    }

                    return $file->panel()->route();
                }
            ],

        ]
    ];
};

