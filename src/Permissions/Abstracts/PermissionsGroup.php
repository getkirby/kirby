<?php

namespace Kirby\Permissions\Abstracts;

use Kirby\Permissions\RoleMatrix;

abstract class PermissionsGroup extends PermissionsFoundation
{
	public static function fromArray(array $array, string $role = '*'): static
	{
		$instance = static::instanceFromArray($array);
		$array    = static::acceptedArguments($array);

		foreach ($array as $key => $matrix) {
			$instance->$key = RoleMatrix::toPermission($matrix, $role);
		}

		return $instance;
	}

	public static function fromWildcard(array|bool $wildcard, string $role = '*'): static
	{
		$permission = RoleMatrix::toPermission($wildcard, $role);
		$args       = static::prefilledArguments($permission);

		return new static(...$args);
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
