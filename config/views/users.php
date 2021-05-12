<?php

return function ($roleId = null) use ($kirby) {

    $roles = $kirby->roles();

    return [
        'component' => 'UsersView',
        'props' => [
            'role' => function () use ($kirby, $roles, $roleId) {
                if ($roleId && $role = $roles->find($roleId)) {
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
            'users' => function () use ($kirby, $roleId) {

                $users = $kirby->users();

                if (empty($roleId) === false) {
                    $users = $users->role($roleId);
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
