<?php

namespace Kirby\Kql\Interceptors\Cms;

class Page extends Model
{
	public const CLASS_ALIAS = 'page';

	protected $toArray = [
		'children',
		'content',
		'drafts',
		'files',
		'id',
		'intendedTemplate',
		'isHomePage',
		'isErrorPage',
		'num',
		'template',
		'title',
		'slug',
		'status',
		'uid',
		'url'
	];

	public function allowedMethods(): array
	{
		return array_merge(
			$this->allowedMethodsForChildren(),
			$this->allowedMethodsForFiles(),
			$this->allowedMethodsForModels(),
			$this->allowedMethodsForParents(),
			$this->allowedMethodsForSiblings(),
			[
				'blueprints',
				'depth',
				'hasTemplate',
				'intendedTemplate',
				'isDraft',
				'isErrorPage',
				'isHomePage',
				'isHomeOrErrorPage',
				'isListed',
				'isReadable',
				'isSortable',
				'isUnlisted',
				'num',
				'slug',
				'status',
				'template',
				'title',
				'uid',
				'uri',
			]
		);
	}

	public function intendedTemplate(): string
	{
		return $this->object->intendedTemplate()->name();
	}

	public function template(): string
	{
		return $this->object->template()->name();
	}
}
