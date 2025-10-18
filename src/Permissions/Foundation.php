<?php

namespace Kirby\Permissions;

use Kirby\Reflection\Constructor;

abstract class Foundation
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
		return (new static())->wildcard(true);
	}

	public static function forNobody(): static
	{
		return (new static())->wildcard(false);
	}

	abstract public static function from(array|bool|null $permissions): static;

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

	abstract public function toArray(): array;
	abstract public function wildcard(bool|null $wildcard): static;
}
