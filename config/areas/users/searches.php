<?php

use Kirby\Cms\App;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\I18n;

return [
    'users' => [
        'label' => I18n::translate('users'),
        'icon'  => 'users',
        'query' => function (string $query = null) {
            $users   = App::instance()->users()->search($query)->limit(10);
            $results = [];

            foreach ($users as $user) {
                $results[] = [
                    'image' => $user->panel()->image(),
                    'text'  => Escape::html($user->username()),
                    'link'  => $user->panel()->url(true),
                    'info'  => Escape::html($user->role()->title())
                ];
            }

            return $results;
        }
    ]
];
