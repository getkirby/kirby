<?php

namespace Kirby\Kql\Interceptors\Cms;

class Structure extends Collection
{
	public const CLASS_ALIAS = 'structure';

	public function toArray(): array
	{
		return $this->object->toArray();
	}
}
