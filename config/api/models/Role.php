<?php

use Kirby\Cms\Role;

/**
 * Role
 */
return [
	'fields' => [
		'description' => fn (Role $role) => $role->description(),
		'name'        => fn (Role $role) => $role->name(),
		'permissions' => fn (Role $role) => $role->permissions()->toArray(),
		'title'       => fn (Role $role) => $role->title(),
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
