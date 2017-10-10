<?php

return function ($user) {

    $output = [
        'id'   => $user->id(),
        'data' => $user->data()->not('password', 'role')->toArray(),
        'role' => $user->role(),
        'image' => [
            'url' => $user->avatar()->url()
        ]
    ];

    return $output;

};
