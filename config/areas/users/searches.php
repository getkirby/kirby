<?php

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
                    'text'  => $user->username(),
                    'link'  => $user->panel()->url(true),
                    'info'  => $user->role()->title()
                ];
            }

            return $results;
        }
    ]
];
