<?php

namespace Kirby\Kql\Interceptors\Cms;

class StructureObject extends Model
{
	public const CLASS_ALIAS = 'structureItem';

	public function allowedMethods(): array
	{
		return array_merge(
			$this->allowedMethodsForSiblings(),
			[
				'content',
				'id',
				'parent',
			]
		);
	}
}
