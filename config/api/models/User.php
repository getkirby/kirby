<?php

use Kirby\Cms\Form;
use Kirby\Cms\User;

/**
 * User
 */
return [
    'default' => function () {
        return $this->user();
    },
    'fields' => [
        'avatar' => function (User $user) {
            return $user->avatar() ? $user->avatar()->crop(512) : null;
        },
        'blueprint' => function (User $user) {
            return $user->blueprint();
        },
        'content' => function (User $user) {
            return Form::for($user)->values();
        },
        'email' => function (User $user) {
            return $user->email();
        },
        'files' => function (User $user) {
            return $user->files()->sortBy('sort', 'asc', 'filename', 'asc');
        },
        'id' => function (User $user) {
            return $user->id();
        },
        'language' => function (User $user) {
            return $user->language();
        },
        'name' => function (User $user) {
            return $user->name()->value();
        },
        'next' => function (User $user) {
            return $user->next();
        },
        'options' => function (User $user) {
            return $user->panelOptions();
        },
        'permissions' => function (User $user) {
            return $user->role()->permissions()->toArray();
        },
        'prev' => function (User $user) {
            return $user->prev();
        },
        'role' => function (User $user) {
            return $user->role();
        },
        'username' => function (User $user) {
            return $user->username();
        }
    ],
    'type'  => 'Kirby\Cms\User',
    'views' => [
        'default' => [
            'avatar',
            'content',
            'email',
            'id',
            'language',
            'name',
            'next' => 'compact',
            'options',
            'prev' => 'compact',
            'role',
            'username'
        ],
        'compact' => [
            'avatar' => 'compact',
            'id',
            'email',
            'language',
            'name',
            'role' => 'compact',
            'username'
        ],
        'auth' => [
            'avatar' => 'compact',
            'permissions',
            'email',
            'id',
            'name',
            'role',
            'language'
        ],
        'panel' => [
            'avatar' => 'compact',
            'blueprint',
            'content',
            'email',
            'id',
            'language',
            'name',
            'next' => ['id', 'name'],
            'options',
            'prev' => ['id', 'name'],
            'role',
            'username',
        ],
    ]
];
