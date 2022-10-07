<?php

namespace Kirby\Kql\Interceptors\Cms;

use Kirby\Kql\Interceptors\Interceptor;

class Collection extends Interceptor
{
	public const CLASS_ALIAS = 'collection';

	public function allowedMethods(): array
	{
		return [
			'chunk',
			'count',
			'filterBy',
			'find',
			'findBy',
			'findByKey',
			'first',
			'flip',
			'groupBy',
			'has',
			'isEmpty',
			'isEven',
			'isNotEmpty',
			'isOdd',
			'keys',
			'last',
			'limit',
			'next',
			'not',
			'nth',
			'offset',
			'pagination',
			'pluck',
			'prev',
			'shuffle',
			'slice',
			'sortBy',
			'without',
		];
	}

	public function toArray(): array
	{
		return $this->object->keys();
	}
}
