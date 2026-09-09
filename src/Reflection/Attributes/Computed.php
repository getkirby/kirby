<?php

namespace Kirby\Reflection\Attributes;

use Attribute;

/**
 * Marks a property whose getter computes its value instead of
 * returning a real default. `Kirby\Reflection\Props` then documents
 * the constructor default, or the `default` passed to the attribute.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Computed
{
	public function __construct(
		public mixed $default = null
	) {
	}
}
