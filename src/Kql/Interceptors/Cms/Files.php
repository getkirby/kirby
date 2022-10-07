<?php

namespace Kirby\Kql\Interceptors\Cms;

class Files extends Collection
{
	public const CLASS_ALIAS = 'files';

	public function allowedMethods(): array
	{
		return array_merge(
			parent::allowedMethods(),
			[
				'template'
			]
		);
	}
}
