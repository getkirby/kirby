<?php

use Kirby\Cms\System;

/**
 * Avatar
 */
return [
    'fields' => [
        'isOk' => function (System $system) {
            return $system->isOk();
        },
        'isInstalled' => function (System $system) {
            return $system->isInstalled();
        },
        'isLocal' => function (System $system) {
            return $system->isLocal();
        },
        'languages' => function () {
            return $this->kirby()->languages();
        },
        'license' => function (System $system) {
            $license = $system->license();
            return $license ? $license['type'] : null;
        },
        'requirements' => function (System $system) {
            return $system->toArray();
        },
        'title' => function () {
            return $this->site()->title()->value();
        },
        'translation' => function () {
            if ($user = $this->user()) {
                return $this->kirby()->translation($user->language());
            }

            return $this->kirby()->translation();
        },
        'user' => function () {
            return $this->user();
        },
    ],
    'type'   => System::class,
    'views'  => [
        'panel' => [
            'isOk',
            'isInstalled',
            'isLocal',
            'languages' => 'compact',
            'license',
            'requirements',
            'title',
            'translation',
            'user' => 'auth'
        ]
    ],
];
