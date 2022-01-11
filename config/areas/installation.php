<?php

use Kirby\Panel\Panel;

return function ($kirby) {
    return [
        'icon'  => 'settings',
        'label' => t('view.installation'),
        'views' => [
            'installation' => [
                'pattern' => 'installation',
                'auth'    => false,
                'action'  => function () use ($kirby) {
                    $system = $kirby->system();
                    return [
                        'component' => 'k-installation-view',
                        'props'     => [
                            'isInstallable' => $system->isInstallable(),
                            'isInstalled'   => $system->isInstalled(),
                            'isOk'          => $system->isOk(),
                            'requirements'  => $system->status(),
                            'translations'  => $kirby->translations()->values(function ($translation) {
                                return [
                                    'text'  => $translation->name(),
                                    'value' => $translation->code(),
                                ];
                            }),
                        ]
                    ];
                }
            ],
            'installation.fallback' => [
                'pattern' => '(:all)',
                'auth'    => false,
                'action'  => fn () => Panel::go('installation')
            ]
        ]
    ];
};
