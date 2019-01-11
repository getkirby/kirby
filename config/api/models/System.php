<?php

use Kirby\Cms\System;

/**
 * System
 */
return [
    'fields' => [
        'isOk' => function (System $system) {
            return $system->isOk();
        },
        'isInstallable' => function (System $system) {
            return $system->isInstallable();
        },
        'isInstalled' => function (System $system) {
            return $system->isInstalled();
        },
        'isLocal' => function (System $system) {
            return $system->isLocal();
        },
        'multilang' => function () {
            return $this->kirby()->option('languages', false) !== false;
        },
        'languages' => function () {
            return $this->kirby()->languages();
        },
        'license' => function (System $system) {
            return $system->license();
        },
        'requirements' => function (System $system) {
            return $system->toArray();
        },
        'breadcrumbTitle' => function () {
            return $this->site()->blueprint()->title();
        },
        'title' => function () {
            return $this->site()->title()->value();
        },
        'translation' => function () {
            if ($user = $this->user()) {
                $translationCode = $user->language();
            } else {
                $translationCode = $this->kirby()->option('panel.language', 'en');
            }

            if ($translation = $this->kirby()->translation($translationCode)) {
                return $translation;
            } else {
                return $this->kirby()->translation('en');
            }
        },
        'kirbytext' => function () {
            return $this->kirby()->option('panel')['kirbytext'] ?? true;
        },
        'user' => function () {
            return $this->user();
        },
        'version' => function () {
            return $this->kirby()->version();
        }
    ],
    'type'   => System::class,
    'views'  => [
        'login' => [
            'isOk',
            'isInstalled',
            'title',
            'translation'
        ],
        'troubleshooting' => [
            'isOk',
            'isInstallable',
            'isInstalled',
            'title',
            'translation',
            'requirements'
        ],
        'panel' => [
            'breadcrumbTitle',
            'isOk',
            'isInstalled',
            'isLocal',
            'kirbytext',
            'languages' => 'compact',
            'license',
            'multilang',
            'requirements',
            'title',
            'translation',
            'user' => 'auth',
                'version'
        ]
    ],
];
