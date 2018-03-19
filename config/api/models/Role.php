<?php

use Kirby\Cms\Role;

/**
 * Role
 */
return [
    'fields' => [
        'name' => function (Role $role) {
            return $role->name();
        },
        'title' => function (Role $role) {
            return $role->title();
        },
    ],
    'type'  => Role::class,
    'views' => [
        'compact' => [
            'name',
            'title'
        ]
    ]
];
