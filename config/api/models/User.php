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
            return $user->avatar();
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
        'id' => function (User $user) {
            return $user->id();
        },
        'language' => function (User $user) {
            return $user->language();
        },
        'name' => function (User $user) {
            return $user->name();
        },
        'next' => function (User $user) {
            return $user->next();
        },
        'options' => function (User $user) {
            if ($blueprint = $user->blueprint()) {
                return $blueprint->options()->toArray();
            }

            return null;
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
    'type'  => User::class,
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
            'role'
        ],
        'compact' => [
            'avatar' => 'compact',
            'id',
            'email',
            'language',
            'name',
            'role',
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
