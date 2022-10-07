<?php

namespace Kirby\Kql\Interceptors\Cms;

class LayoutColumn extends Model
{
	public const CLASS_ALIAS = 'layoutColumn';

	protected $toArray = [
		'blocks',
		'id',
		'isEmpty',
		'width',
	];

	public function allowedMethods(): array
	{
		return array_merge(
			$this->allowedMethodsForSiblings(),
			[
				'blocks',
				'id',
				'isEmpty',
				'isNotEmpty',
				'span',
				'width'
			]
		);
	}
}
