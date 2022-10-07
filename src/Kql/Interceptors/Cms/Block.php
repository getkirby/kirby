<?php

namespace Kirby\Kql\Interceptors\Cms;

class Block extends Model
{
	public const CLASS_ALIAS = 'block';

	protected $toArray = [
		'content',
		'id',
		'isEmpty',
		'isHidden',
		'type'
	];

	public function allowedMethods(): array
	{
		return array_merge(
			$this->allowedMethodsForSiblings(),
			[
				'content',
				'id',
				'isEmpty',
				'isHidden',
				'isNotEmpty',
				'toField',
				'toHtml',
				'parent',
				'type'
			]
		);
	}
}
