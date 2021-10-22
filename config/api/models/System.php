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
        'authStatus' => function () {
            return $this->kirby()->auth()->status()->toArray();
        },
        'defaultLanguage' => function () {
            return $this->kirby()->panelLanguage();
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
        'locales' => function () {
            $locales = [];
            $translations = $this->kirby()->translations();
            foreach ($translations as $translation) {
                $locales[$translation->code()] = $translation->locale();
            }
            return $locales;
        },
        'loginMethods' => function (System $system) {
            return array_keys($system->loginMethods());
        },
        'requirements' => function (System $system) {
            return $system->toArray();
        },
        'site' => function (System $system) {
            return $system->title();
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
                $translationCode = $this->kirby()->panelLanguage();
            }

            if ($translation = $this->kirby()->translation($translationCode)) {
                return $translation;
            } else {
                return $this->kirby()->translation('en');
            }
        },
        'kirbytext' => function () {
            return $this->kirby()->option('panel.kirbytext') ?? true;
        },
        'user' => function () {
            return $this->user();
        },
        'version' => function () {
            $user = $this->user();

            if ($user && $user->role()->permissions()->for('access', 'system') === true) {
                return $this->kirby()->version();
            } else {
                return null;
            }
        }
    ],
    'type'   => 'Kirby\Cms\System',
    'views'  => [
        'login' => [
            'authStatus',
            'isOk',
            'isInstallable',
            'isInstalled',
            'loginMethods',
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
            'defaultLanguage',
            'isOk',
            'isInstalled',
            'isLocal',
            'kirbytext',
            'languages',
            'license',
            'locales',
            'multilang',
            'requirements',
            'site',
            'slugs',
            'title',
            'translation',
            'user' => 'auth',
            'version'
        ]
    ],
];
