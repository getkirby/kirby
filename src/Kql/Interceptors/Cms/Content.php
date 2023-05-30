<?php

namespace Kirby\Kql\Interceptors\Cms;

use Kirby\Kql\Interceptor;

class Content extends Interceptor
{
	public const CLASS_ALIAS = 'content';

	public function __call(string $method, array $args = []): mixed
	{
		if ($this->isAllowedMethod($method) === true) {
			return $this->object->$method(...$args);
		}

		if (method_exists($this->object, $method) === false) {
			return $this->object->get($method);
		}

		$this->forbiddenMethod($method);
	}

	public function allowedMethods(): array
	{
		return [
			'data',
			'fields',
			'has',
			'get',
			'keys',
			'not',
		];
	}

	public function toArray(): array
	{
		return $this->object->toArray();
	}
}
