<?php

namespace Kirby\Kql\Interceptors\Cms;

class Users extends Collection
{
	public const CLASS_ALIAS = 'users';

	public function allowedMethods(): array
	{
		return array_merge(
			parent::allowedMethods(),
			[
				'role'
			]
		);
	}
}
