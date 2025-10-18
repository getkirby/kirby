<?php

namespace Kirby\Permissions\Sandbox;

class User
{
	public static self $current;

	public function __construct(
		protected Role $role
	) {
	}

	public static function current(): static|null
	{
		return static::$current ?? null;
	}

	public static function ensure(self|null $user = null): static
	{
		return $user ?? static::current() ?? static::nobody();
	}

	public static function nobody(): static
	{
		return new static(
			role: new Role('nobody')
		);
	}

	public function role(): Role
	{
		return $this->role;
	}
}
