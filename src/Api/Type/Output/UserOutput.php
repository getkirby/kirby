<?php

use Kirby\Api\Type;

return function () {

    return [
        'name' => 'User',
        'fields' => [
            'email' => [
                'type'    => Type::string(),
                'resolve' => function ($user) {
                    return $user->email()->value();
                }
            ],
            'firstName' => [
                'type'    => Type::string(),
                'resolve' => function ($user) {
                    return $user->firstName()->value();
                }
            ],
            'lastName' => [
                'type'    => Type::string(),
                'resolve' => function ($user) {
                    return $user->lastName()->value();
                }
            ],
            'role' => [
                'type'    => Type::string(),
                'resolve' => function ($user) {
                    return $user->role()->value();
                }
            ],
            'image' => [
                'type'    => Type::avatar(),
                'resolve' => function ($user) {
                    return $user->avatar();
                }
            ]
        ]
    ];

};
