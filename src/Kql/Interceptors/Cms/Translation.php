<?php

namespace Kirby\Kql\Interceptors\Cms;

use Kirby\Kql\Interceptors\Interceptor;

class Translation extends Interceptor
{
	public const CLASS_ALIAS = 'translation';

	protected $toArray = [
		'code',
		'data',
		'direction',
		'id',
		'name',
		'locale',
		'author'
	];

	public function allowedMethods(): array
	{
		return [
			'code',
			'data',
			'dataWithFallback',
			'direction',
			'get',
			'id',
			'name',
			'locale',
			'author'
		];
	}
}
