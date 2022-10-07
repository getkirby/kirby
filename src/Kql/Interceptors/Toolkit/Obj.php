<?php

namespace Kirby\Kql\Interceptors\Toolkit;

use Kirby\Kql\Interceptors\Interceptor;

class Obj extends Interceptor
{
	public const CLASS_ALIAS = 'obj';

	public function allowedMethods(): array
	{
		return [
			'get',
			'toArray',
			'toJson',
		];
	}

	public function toArray(): array
	{
		return $this->object->toArray();
	}
}
