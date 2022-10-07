<?php

namespace Kirby\Kql\Interceptors\Cms;

use Kirby\Kql\Interceptors\Interceptor;

class Content extends Interceptor
{
	public const CLASS_ALIAS = 'content';

	public function __call($method, array $args = [])
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
