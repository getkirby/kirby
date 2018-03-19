<?php

use Kirby\Cms\Role;

/**
 * Role
 */
return [
    'fields' => [
        'description' => function (Role $role) {
            return $role->description();
        },
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
            'description',
            'name',
            'title'
        ]
    ]
];
