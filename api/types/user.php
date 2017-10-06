<?php

return function ($user, $arguments) {

    $data = $user->data()->toArray();

    return [
        'id'        => $user->id(),
        'email'     => $data['email'],
        'firstName' => $data['firstName'] ?? null,
        'lastName'  => $data['lastName'] ?? null,
        'role'      => $data['role'] ?? 'nobody',
        'image'     => [
            'url' => $user->avatar()->url()
        ]
    ];

};
