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
        'permissions' => function (Role $role) {
            return $role->permissions()->toArray();
        },
        'title' => function (Role $role) {
            return $role->title();
        },
    ],
    'type'  => 'Kirby\Cms\Role',
    'views' => [
        'compact' => [
            'description',
            'name',
            'title'
        ]
    ]
];
