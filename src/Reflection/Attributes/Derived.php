<?php

namespace Kirby\Reflection\Attributes;

use Attribute;

/**
 * Marks a property whose value is computed at runtime.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Derived
{
	public function __construct(
		public mixed $default = null
	) {
	}
}
