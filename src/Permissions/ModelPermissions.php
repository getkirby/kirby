<?php

namespace Kirby\Permissions;

use Kirby\Toolkit\Reflection;

class ModelPermissions
{
	public function __construct(
		public bool|null $access = null,
		public bool|null $create = null,
		public bool|null $delete = null,
		public bool|null $list = null,
		public bool|null $read = null,
		public bool|null $update = null,
	) {
	}

	public function __call(string $capability, array $arguments = []): mixed
	{
		return $this->$capability;
	}

	public static function from(array|bool $permissions, string $role = '*'): static
	{
		if (is_bool($permissions) === true) {
			return static::fromWildcard($permissions);
		}

		if (isset($permissions['*']) === true) {
			$instance = static::fromWildcard($permissions['*'], $role);
		} else {
			$instance = new static();
		}

		$props = Reflection::extractProps($permissions, static::class);

		foreach ($props as $key => $value) {
			$instance->$key = static::resolve($value, $role);
		}

		return $instance;
	}

	public static function fromWildcard(array|bool $wildcard, string $role = '*'): static
	{
		$permission = static::resolve($wildcard, $role);
		$props      = array_fill_keys(Reflection::paramsNames(static::class), $permission);

		return new static(...$props);
	}

	public function merge(self $permissions): static
	{
		$props = Reflection::paramsNames(static::class);

		foreach ($props as $key) {
			if ($permissions->$key !== null) {
				$this->$key = $permissions->$key;
			}
		}

		return $this;
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

	public function toArray(): array
	{
		$props = [];

		foreach (Reflection::paramsNames(static::class) as $param) {
			$props[$param] = $this->$param;
		}

		return $props;
	}
}
