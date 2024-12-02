<?php

namespace Kirby\Form\Mixin;

trait Endpoints
{
	/**
	 * Routes for the field API
	 */
	public function api(): array
	{
		return [];
	}

	/**
	 * Returns optional dialog routes for the field
	 */
	public function dialogs(): array
	{
		return [];
	}

	/**
	 * Returns optional drawer routes for the field
	 */
	public function drawers(): array
	{
		return [];
	}

	/**
	 * @deprecated 5.0.0 Use `::api()` instead
	 */
	public function routes(): array
	{
		return $this->api();
	}
}
