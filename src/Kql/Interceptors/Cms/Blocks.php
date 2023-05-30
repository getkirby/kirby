<?php

namespace Kirby\Kql\Interceptors\Cms;

class Blocks extends Collection
{
	public const CLASS_ALIAS = 'blocks';

	public function allowedMethods(): array
	{
		return array_merge(
			parent::allowedMethods(),
			[
				'excerpt',
				'toHtml'
			]
		);
	}

	public function toArray(): array
	{
		return $this->object->toArray();
	}
}
