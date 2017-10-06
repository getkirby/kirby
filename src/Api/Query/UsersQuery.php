<?php

use Kirby\Api\Type;

return function ($users) {
    return [
        'description' => 'Fetches multiple users',
        'type'        => Type::users(),
        'args' => [
            'role' => [
                'type' => Type::string()
            ],
            'page' => [
                'type' => Type::int(),
                'defaultValue' => 1
            ],
            'limit' => [
                'type' => Type::int(),
                'defaultValue' => 10
            ]
        ],
        'resolve' => function ($root, $args) use ($users) {
            if (!empty($args['role'])) {
                $users = $users->filterBy('role', '==', $args['role']);
            }

            return $users->paginate($args['limit'], $args['page']);
        }
    ];
};
