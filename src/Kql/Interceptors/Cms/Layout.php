<?php

namespace Kirby\Kql\Interceptors\Cms;

class Layout extends Model
{
	public const CLASS_ALIAS = 'layout';

	protected $toArray = [
		'attrs',
		'columns',
		'id',
		'isEmpty',
	];

	public function allowedMethods(): array
	{
		return array_merge(
			$this->allowedMethodsForSiblings(),
			[
				'attrs',
				'columns',
				'id',
				'isEmpty',
				'isNotEmpty',
				'parent'
			]
		);
	}
}
