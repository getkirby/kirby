<?php

use Kirby\Api\Type;

return function ($users) {
    return [
        'description' => 'Fetch a single user by email address',
        'type'        => Type::user(),
        'args' => [
            'email' => [
                'type' => Type::string()
            ]
        ],
        'resolve' => function ($root, $args) use ($users) {
            return $users->findBy('email', $args['email']);
        }
    ];
};
