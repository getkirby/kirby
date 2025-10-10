<?php

namespace Kirby\Permissions\Abstracts;

use Kirby\Reflection\Constructor;

abstract class PermissionsFoundation
{
	public function __call(string $key, array $arguments = []): mixed
	{
		return $this->$key;
	}

	protected static function acceptedArguments(array $arguments): array
	{
		return (new Constructor(static::class))->getAcceptedArguments($arguments);
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

		return static::fromArray($permissions, $role);
	}

	abstract public static function fromArray(array $args, string $role = '*'): static;

	abstract public static function fromWildcard(bool $wildcard): static;

	protected static function instanceFromArray(array $array): static
	{
		if (isset($array['*']) === true) {
			return static::fromWildcard($array['*']);
		}

		return new static();
	}

	public static function keys(): array
	{
		return (new Constructor(static::class))->getParameterNames(static::class);
	}

	protected static function prefilledArguments(bool $value): array
	{
		return array_fill_keys(static::keys(), $value);
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
}
