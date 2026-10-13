<?php

namespace Kirby\Form\Mixin;

use Kirby\Reflection\Attributes\Prop;

/**
 * Provides the `type` prop and accessor for the type
 * the field was registered with
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
trait Type
{
	/**
	 * Type of the field
	 */
	#[Prop(computed: true)]
	protected string|null $type = null;

	/**
	 * Returns the type the field
	 */
	public function type(): string
	{
		if ($this->type !== null) {
			return $this->type;
		}

		$class = basename(str_replace('\\', '/', static::class));
		return strtolower(preg_replace('/Field$/', '', $class));
	}
}
