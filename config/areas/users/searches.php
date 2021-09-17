<?php

use Kirby\Toolkit\Escape;

return [
    'users' => [
        'label' => t('users'),
        'icon'  => 'users',
        'query' => function (string $query = null) {
            $users   = kirby()->users()->search($query)->limit(10);
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
