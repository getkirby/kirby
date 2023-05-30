<?php

namespace Kirby\Kql\Interceptors\Cms;

use Kirby\Kql\Interceptor;

class App extends Interceptor
{
	public const CLASS_ALIAS = 'kirby';

	protected array $toArray = [
		'site',
		'url'
	];

	public function allowedMethods(): array
	{
		return [
			'collection',
			'defaultLanguage',
			'detectedLanguage',
			'draft',
			'file',
			'language',
			'languageCode',
			'languages',
			'multilang',
			'page',
			'roles',
			'site',
			'translation',
			'translations',
			'url',
			'user',
			'users',
			'version'
		];
	}
}
