<?php

namespace Kirby\Form\Mixin;

trait Name
{
	protected string|null $name;

	public function name(): string
	{
		return strtolower($this->name ?? $this->type());
	}

	protected function setName(string|null $name): void
	{
		$this->name = $name;
	}
}
