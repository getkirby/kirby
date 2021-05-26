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
                            'version' => $kirby->version(),
                        ]
                    ];
                }
            ],
        ]
    ];
};
