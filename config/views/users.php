<?php

/** @var \Kirby\Cms\App $kirby */

return function (string $role = null) use ($kirby) {
    $roles = $kirby->roles();

    return [
        'component' => 'UsersView',
        'props' => [
            'role' => function () use ($kirby, $roles, $role) {
                if ($role && $role = $roles->find($role)) {
                    return [
                        'id'    => $role->id(),
                        'title' => $role->title()
                    ];
                }
            },
            'roles' => Inertia::collect($roles, function ($role) {
                return [
                    'id'    => $role->id(),
                    'title' => $role->title(),
                ];
            }),
            'users' => function () use ($kirby, $role) {
                $users = $kirby->users();

                if (empty($role) === false) {
                    $users = $users->role($role);
                }

                return Inertia::collection($users, function ($user) {
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
                }, [
                    'limit' => 20,
                    'page'  => get('page')
                ]);
            },
        ],
        'view' => 'users'
    ];
};
