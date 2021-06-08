<?php

use Kirby\Panel\Panel;

return function ($kirby) {
    return [
        'icon'   => 'users',
        'label'  => t('view.users'),
        'search' => 'users',
        'menu'   => true,
        'routes' => [
            [
                'pattern' => 'users',
                'action'  => function () use ($kirby) {
                    $role  = get('role');
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
                                            'icon'  => $user->panel()->icon(),
                                            'id'    => $user->id(),
                                            'image' => $user->panel()->image(),
                                            'info'  => $user->role()->title(),
                                            'link'  => $user->panel()->url(true),
                                            'text'  => $user->username(),
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
                    return Panel::user($id)->panel()->route();
                }
            ],
            [
                'pattern' => 'users/(:any)/files/(:any)',
                'action'  => function (string $id, string $filename) use ($kirby) {
                    $user     = Panel::user($id);
                    $filename = urldecode($filename);

                    if (!$file = $user->file($filename)) {
                        return t('error.file.undefined');
                    }

                    return $file->panel()->route();
                }
            ],
        ]
    ];
};
