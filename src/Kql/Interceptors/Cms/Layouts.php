<?php

namespace Kirby\Kql\Interceptors\Cms;

class Layouts extends Collection
{
	public const CLASS_ALIAS = 'layouts';

	public function toArray(): array
	{
		return $this->object->toArray();
	}
}
