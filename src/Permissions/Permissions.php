<?php

namespace Kirby\Permissions;

use Kirby\Reflection\Constructor;

abstract class Permissions
{
	public function __call(string $key, array $arguments = []): mixed
	{
		return $this->$key;
	}

	abstract public function __construct();

	protected static function acceptedArguments(array $arguments): array
	{
		return (new Constructor(static::class))->getAcceptedArguments($arguments);
	}

	/**
	 * Creates a new instance from array, boolean or null. This is mainly
	 * useful to translate blueprint options to permission settings.
	 */
	public static function from(array|bool|null $permissions): static
	{
		$instance = new static();

		if ($permissions === true || $permissions === false || $permissions === null) {
			return $instance->wildcard($permissions);
		}

		if (isset($permissions['*']) === true) {
			$instance->wildcard($permissions['*']);
		}

		foreach (static::acceptedArguments($permissions) as $key => $value) {
			$instance->$key = $value;
		}

		return $instance;
	}

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

	/**
	 * Converts all permissions to an array for debugging
	 */
	public function toArray(): array
	{
		$props = [];

		foreach (static::keys() as $param) {
			$props[$param] = $this->$param;
		}

		return $props;
	}

	/**
	 * Sets all permissions to either `true`, `false` or `null`
	 * as a starting point for more fine-grained permission definitions
	 * or as a wildcard option for admins or nobodies.
	 */
	public function wildcard(bool|null $wildcard): static
	{
		foreach (static::keys() as $key) {
			$this->$key = $wildcard;
		}

		return $this;
	}
}
