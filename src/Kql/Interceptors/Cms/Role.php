<?php

namespace Kirby\Kql\Interceptors\Cms;

use Kirby\Kql\Interceptor;

class Role extends Interceptor
{
	public const CLASS_ALIAS = 'role';

	protected array $toArray = [
		'description',
		'id',
		'name',
		'title',
	];

	public function allowedMethods(): array
	{
		return [
			'description',
			'id',
			'name',
			'permissions',
			'title'
		];
	}

	public function permissions(): array
	{
		return $this->object->permissions()->toArray();
	}
}
