<?php

/** @var \Kirby\Cms\App $kirby */

return function () use ($kirby) {
    $system = $kirby->system();

    return [
        'component' => 'InstallationView',
        'props' => [
            'isInstallable' => $system->isInstallable(),
            'isInstalled'   => $system->isInstalled(),
            'isOk'          => $system->isOk(),
            'requirements'  => $system->status(),
            'translations'  => Inertia::toValues($kirby->translations(), function ($translation) {
                return [
                    'text'  => $translation->name(),
                    'value' => $translation->code(),
                ];
            }),
        ],
        'view' => 'installation'
    ];
};
