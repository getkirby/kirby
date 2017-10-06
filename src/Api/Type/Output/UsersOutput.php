<?php

use Kirby\Api\Type;

return function () {

    return [
        'name' => 'Users',
        'fields' => [
            'pagination' => [
                'type'    => Type::pagination(),
                'resolve' => function ($users) {
                    return $users->pagination();
                }
            ],
            'items' => [
                'type'    => Type::listOf(Type::user()),
                'resolve' => function ($users) {
                    return $users;
                }
            ]
        ]
    ];

};
