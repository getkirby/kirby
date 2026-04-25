<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `name` prop and accessor for the field's identifier
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Name
{
	protected string|null $name;

	public function name(): string
	{
		return strtolower($this->name ?? $this->type());
	}
}
