<?php

namespace Kirby\Permissions;

class RoleMatrix
{
	public static function toPermission(array|bool $matrix, string $role = '*'): bool
	{
		if (is_bool($matrix) === true) {
			return $matrix;
		}

		if (isset($matrix[$role]) === true) {
			return $matrix[$role];
		}

		return $matrix['*'] ?? true;
	}
}
