<?php

namespace Kirby\Kql\Interceptors\Cms;

class Site extends Model
{
	public const CLASS_ALIAS = 'site';

	protected $toArray = [
		'children',
		'drafts',
		'files',
		'title',
		'url',
	];

	public function allowedMethods(): array
	{
		return array_merge(
			$this->allowedMethodsForChildren(),
			$this->allowedMethodsForFiles(),
			$this->allowedMethodsForModels(),
			[
				'blueprints',
				'breadcrumb',
				'errorPage',
				'errorPageId',
				'homePage',
				'homePageId',
				'page',
				'pages',
				'title',
			]
		);
	}
}
