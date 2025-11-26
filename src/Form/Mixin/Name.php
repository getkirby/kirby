<?php

namespace Kirby\Form\Mixin;

trait Name
{
	protected string|null $name;

	public function name(): string
	{
		return strtolower($this->name ?? $this->type());
	}
}
