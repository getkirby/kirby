<?php

return function () use ($kirby) {

    return [
        'component' => 'SettingsView',
        'props' => [
            'languages' => Inertia::collect($kirby->languages(), function ($language) {
                return [
                    'default' => $language->isDefault(),
                    'icon' => [
                        'back' => 'black',
                        'type' => 'globe',
                    ],
                    'id' => $language->code(),
                    'image' => true,
                    'info' => $language->code(),
                    'text' => $language->name(),
                ];
            }),
            'version' => $kirby->version(),
        ],
        'view' => 'settings'
    ];

};
