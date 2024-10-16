<?php

namespace Kirby\Form\Mixin;

trait Api
{
	public function api(): array
	{
		return $this->routes();
	}

	/**
	 * Routes for the field API
	 */
	public function routes(): array
	{
		return [];
	}
}
