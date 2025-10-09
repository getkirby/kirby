<?php

namespace Kirby\Permissions;

use Kirby\Reflection\Constructor;

abstract class Foundation
{
	public function __call(string $key, array $arguments = []): mixed
	{
		return $this->$key;
	}

	public static function forAdmin(): static
	{
		return static::fromWildcard(true);
	}

	public static function forNobody(): static
	{
		return static::fromWildcard(false);
	}

	public static function from(array|bool $permissions, string $role = '*'): static
	{
		if (is_bool($permissions) === true) {
			return static::fromWildcard($permissions);
		}

		return static::fromArgs($permissions, $role);
	}

	abstract public static function fromArgs(array $args, string $role = '*'): static;

	abstract public static function fromWildcard(bool $wildcard): static;

	public static function keys(): array
	{
		return (new Constructor(static::class))->getParameterNames(static::class);
	}

	public function merge(self $permissions): static
	{
		foreach (static::keys() as $key) {
			if ($permissions->$key !== null) {
				$this->$key = $permissions->$key;
			}
		}

		return $this;
	}

	public function toArray(): array
	{
		$props = [];

		foreach (static::keys() as $param) {
			$props[$param] = $this->$param;
		}

		return $props;
	}
}
