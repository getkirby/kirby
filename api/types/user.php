<?php

return function ($user) {

    return [
        'id'   => $user->id(),
        'data' => $user->data()->not('password', 'role')->toArray(),
        'role' => $user->role(),
        'image' => [
            'url' => $user->avatar()->url()
        ]
    ];

};
