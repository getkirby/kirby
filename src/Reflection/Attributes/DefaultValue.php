<?php

namespace Kirby\Reflection\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class DefaultValue
{
	public function __construct(
		protected mixed $value
	) {
	}

	public function value(): mixed
	{
		return $this->value;
	}
}
