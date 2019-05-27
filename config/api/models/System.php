<?php

use Kirby\Cms\System;
use Kirby\Toolkit\Str;

/**
 * System
 */
return [
    'fields' => [
        'ascii' => function () {
            return Str::$ascii;
        },
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
            try {
                return $this->site()->blueprint()->title();
            } catch (Throwable $e) {
                return $this->site()->title()->value();
            }
        },
        'slugs' => function () {
            return Str::$language;
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
            'isInstallable',
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
            'ascii',
            'breadcrumbTitle',
            'isOk',
            'isInstalled',
            'isLocal',
            'kirbytext',
            'languages' => 'compact',
            'license',
            'multilang',
            'requirements',
            'slugs',
            'title',
            'translation',
            'user' => 'auth',
            'version'
        ]
    ],
];
