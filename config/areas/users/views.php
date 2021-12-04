<?php

use Kirby\Cms\Find;
use Kirby\Toolkit\Escape;

return [
    'users' => [
        'pattern' => 'users',
        'action'  => function () {
            $kirby = kirby();
            $role  = get('role');
            $roles = $kirby->roles()->toArray(fn ($role) => [
                'id'    => $role->id(),
                'title' => $role->title(),
            ]);

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
                            'data' => $users->values(fn ($user) => [
                                'id'    => $user->id(),
                                'image' => $user->panel()->image(),
                                'info'  => Escape::html($user->role()->title()),
                                'link'  => $user->panel()->url(true),
                                'text'  => Escape::html($user->username())
                            ]),
                            'pagination' => $users->pagination()->toArray()
                        ];
                    },
                ]
            ];
        }
    ],
    'user' => [
        'pattern' => 'users/(:any)',
        'action'  => function (string $id) {
            return Find::user($id)->panel()->view();
        }
    ],
    'user.file' => [
        'pattern' => 'users/(:any)/files/(:any)',
        'action'  => function (string $id, string $filename) {
            return Find::file('users/' . $id, $filename)->panel()->view();
        }
    ],
];
