<?php

return function () use ($kirby) {
    $system = $kirby->system();

    return [
        'component' => 'InstallationView',
        'props' => [
            'isInstallable' => $system->isInstallable(),
            'isInstalled'   => $system->isInstalled(),
            'isOk'          => $system->isOk(),
            'requirements'  => $system->status(),
            'translations'  => Inertia::collect($kirby->translations(), function ($translation) {
                return [
                    'text'  => $translation->name(),
                    'value' => $translation->code(),
                ];
            }),
        ],
        'view' => 'installation'
    ];

};
