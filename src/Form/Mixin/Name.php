<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `name` prop and accessor for the field's identifier
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Name
{
	/**
	 * Unique name of the field, which is used as key in the content file
	 */
	#[Prop(computed: true)]
	protected string|null $name = null;

	public function name(): string
	{
		return strtolower($this->name ?? $this->type());
	}
}
