<?php

use Kirby\Cms\User;
use Kirby\Form\Form;

/**
 * User
 */
return [
	'default' => fn () => $this->user(),
	'fields' => [
		'avatar'      => fn (User $user) => $user->avatar()?->crop(512),
		'blueprint'   => fn (User $user) => $user->blueprint(),
		'content'     => fn (User $user) => Form::for($user)->toFormValues(),
		'email'       => fn (User $user) => $user->email(),
		'files'       => fn (User $user) => $user->files()->sorted()->filter('isListable', true),
		'id'          => fn (User $user) => $user->id(),
		'language'    => fn (User $user) => $user->language(),
		'name'        => fn (User $user) => $user->name()->value(),
		'next'        => fn (User $user) => $user->next($user->siblings()->filter('isListable', true)),
		'options'     => fn (User $user) => $user->panel()->options(),
		'panelImage'  => fn (User $user) => $user->panel()->image(),
		'permissions' => function (User $user) {
			$role = $user->role();

			if ($role->isAccessible() === false) {
				return null;
			}

			return $role->permissions()->toArray();
		},
		'prev'        => fn (User $user) => $user->prev($user->siblings()->filter('isListable', true)),
		'role'        => function (User $user) {
			$role = $user->role();

			if ($role->isAccessible() === false) {
				return null;
			}

			return $role;
		},
		'roles'       => fn (User $user) => $user->roles(),
		'username'    => fn (User $user) => $user->username(),
		'uuid'        => fn (User $user) => $user->uuid()?->toString()
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
			'role',
			'username',
			'uuid'
		],
		'compact' => [
			'avatar' => 'compact',
			'id',
			'email',
			'language',
			'name',
			'role' => 'compact',
			'username',
			'uuid'
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
			'uuid'
		],
	]
];
