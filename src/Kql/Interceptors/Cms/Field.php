<?php

namespace Kirby\Kql\Interceptors\Cms;

use Kirby\Kql\Interceptors\Interceptor;

class Field extends Interceptor
{
	public const CLASS_ALIAS = 'field';

	public function __call($method, array $args = [])
	{
		if ($this->isAllowedMethod($method) === true) {
			return $this->object->$method(...$args);
		}

		// field methods
		$methods = array_keys($this->object::$methods);
		$method  = strtolower($method);

		if (in_array($method, $methods) === true) {
			return $this->object->$method(...$args);
		}

		// aliases
		$aliases = array_keys($this->object::$aliases);
		$alias   = strtolower($method);

		if (in_array($alias, $aliases) === true) {
			return $this->object->$method(...$args);
		}

		$this->forbiddenMethod($method);
	}

	public function allowedMethods(): array
	{
		return [
			'exists',
			'isEmpty',
			'isNotEmpty',
			'key',
			'or',
			'value'
		];
	}

	public function toResponse()
	{
		return $this->object->toString();
	}
}
