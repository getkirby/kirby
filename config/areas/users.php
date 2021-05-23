<?php

return function ($kirby) {
    return [
        'icon'   => 'users',
        'label'  => t('view.users'),
        'search' => 'users',
        'menu'   => true,
        'routes' => [
            [
                'pattern' => [
                    'users',
                    'users/role/(:any)'
                ],
                'action'  => function (string $role = null) use ($kirby) {
                    $roles = $kirby->roles()->toArray(function ($role) {
                        return [
                            'id'    => $role->id(),
                            'title' => $role->title(),
                        ];
                    });

                    return [
                        'component' => 'k-users-view',
                        'props'     => [
                            'role' => function () use ($kirby, $roles, $role) {
                                if ($role) {
                                    return $roles[$role] ?? null;
                                }
                            },
                            'roles' => array_values($roles),
                            'users' => function () use ($kirby, $role) {
                                $users = $kirby->users();

                                if (empty($role) === false) {
                                    $users = $users->role($role);
                                }

                                $users = $users->paginate([
                                    'limit' => 20,
                                    'page'  => get('page')
                                ]);

                                return [
                                    'data' => $users->values(function ($user) {
                                        return [
                                            'id'   => $user->id(),
                                            'icon' => [
                                                'type' => 'user',
                                                'back' => 'black'
                                            ],
                                            'text'  => $user->username(),
                                            'info'  => $user->role()->title(),
                                            'link'  => 'users/' . $user->id(),
                                            'image' => true
                                        ];
                                    }),
                                    'pagination' => $users->pagination()->toArray()
                                ];
                            },
                        ]
                    ];
                }
            ],
            [
                'pattern' => 'users/(:any)',
                'action'  => function (string $id) use ($kirby) {
                    if (!$user = $kirby->user($id)) {
                        return t('error.user.undefined');
                    }

                    return $user->panel()->route();
                }
            ],
            [
                'pattern' => 'users/(:any)/files/(:any)',
                'action'  => function (string $id, string $filename) use ($kirby) {
                    $filename = urldecode($filename);

                    if (!$user = $kirby->user($id)) {
                        return t('error.user.undefined');
                    }

                    if (!$file = $user->file($filename)) {
                        return t('error.file.undefined');
                    }

                    return $file->panel()->route();
                }
            ],
        ]
    ];
};
