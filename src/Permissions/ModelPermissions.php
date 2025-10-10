<?php

namespace Kirby\Permissions;

use Kirby\Reflection\Constructor;

class ModelPermissions extends Foundation
{
	public static function fromArgs(array $args, string $role = '*'): static
	{
		if (isset($args['*']) === true) {
			$instance = static::fromWildcard($args['*']);
		} else {
			$instance = new static();
		}

		$args = (new Constructor(static::class))->getAcceptedArguments($args);

		foreach ($args as $key => $value) {
			$instance->$key = static::resolve($value, $role);
		}

		return $instance;
	}

	public static function fromWildcard(array|bool $wildcard, string $role = '*'): static
	{
		$permission = static::resolve($wildcard, $role);
		$props      = array_fill_keys(static::keys(), $permission);

		return new static(...$props);
	}

	public static function resolve(array|bool $matrix, string $role = '*'): bool
	{
		if (is_bool($matrix) === true) {
			return $matrix;
		}

		if (isset($matrix[$role]) === true) {
			return $matrix[$role];
		}

		return $matrix['*'] ?? false;
	}
}
