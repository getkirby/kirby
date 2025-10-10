<?php

namespace Kirby\Permissions\Abstracts;

abstract class PermissionsGroups extends PermissionsFoundation
{
	public static function fromArray(array $array, string $role = '*'): static
	{
		$instance = static::instanceFromArray($array);
		$array    = static::acceptedArguments($array);

		foreach ($array as $key => $value) {
			$instance->$key = ($instance->$key)::from($value, $role);
		}

		return $instance;
	}

	public static function fromWildcard(bool $wildcard): static
	{
		$instance = new static();
		$args     = static::prefilledArguments($wildcard);

		foreach ($args as $key => $value) {
			$instance->$key = ($instance->$key)::fromWildcard($value);
		}

		return $instance;
	}

	public function toArray(): array
	{
		$props = [];

		foreach (static::keys() as $param) {
			$props[$param] = $this->$param->toArray();
		}

		return $props;
	}
}
