<?php

namespace Kirby\Permissions;

use Kirby\Reflection\Constructor;

abstract class ModelPermissions extends Foundation
{
	public static function fromArray(array $args, string $role = '*'): static
	{
		if (isset($args['*']) === true) {
			$instance = static::fromWildcard($args['*']);
		} else {
			$instance = new static();
		}

		$args = (new Constructor(static::class))->getAcceptedArguments($args);

		foreach ($args as $key => $matrix) {
			$instance->$key = RoleMatrix::toPermission($matrix, $role);
		}

		return $instance;
	}

	public static function fromWildcard(array|bool $wildcard, string $role = '*'): static
	{
		$permission = RoleMatrix::toPermission($wildcard, $role);
		$props      = array_fill_keys(static::keys(), $permission);

		return new static(...$props);
	}
}
