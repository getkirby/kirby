<?php

namespace Kirby\Form\Mixin;

trait Name
{
	protected string|null $name;

	public function name(): string
	{
		return $this->name ?? $this->type();
	}

	protected function setName(string|null $name = null): void
	{
		$this->name = strtolower($name ?? $this->type());
	}
}
