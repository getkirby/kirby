<?php

namespace Kirby\Kql\Interceptors\Cms;

class User extends Model
{
	public const CLASS_ALIAS = 'user';

	protected $toArray = [
		'id',
		'name',
		'role',
		'username'
	];

	public function allowedMethods(): array
	{
		return array_merge(
			$this->allowedMethodsForFiles(),
			$this->allowedMethodsForModels(),
			$this->allowedMethodsForSiblings(),
			[
				'avatar',
				'email',
				'id',
				'isAdmin',
				'language',
				'modified',
				'name',
				'role',
				'username',
			]
		);
	}
}
