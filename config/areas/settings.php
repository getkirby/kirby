<?php

return function ($kirby) {
    return [
        'icon'   => 'settings',
        'label'  => t('view.settings'),
        'menu'   => true,
        'routes' => [
            [
                'pattern' => 'settings',
                'action'  => function () use ($kirby) {
                    $license = $kirby->system()->license();
                    if ($license === true) {
                        // valid license, but user is not admin
                        $license = 'Kirby 3';
                    } elseif ($license === false) {
                        // no valid license
                        $license = null;
                    }

                    return [
                        'component' => 'k-settings-view',
                        'props'     => [
                            'languages' => $kirby->languages()->values(function ($language) {
                                return [
                                    'default' => $language->isDefault(),
                                    'id'      => $language->code(),
                                    'info'    => $language->code(),
                                    'text'    => $language->name(),
                                ];
                            }),
                            'license' => $license,
                            'version' => $kirby->version(),
                        ]
                    ];
                }
            ],
        ]
    ];
};
