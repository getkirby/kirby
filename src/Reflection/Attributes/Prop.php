<?php

namespace Kirby\Reflection\Attributes;

use Attribute;

/**
 * Marks a property as prop that can be set and exported.
 * `computed` and `default` are only used for the docs.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Prop
{
	public function __construct(
		public bool $computed = false,
		public mixed $default = null,
		public bool|string $export = true,
		public string|null $getter = null,
		public string|null $setter = null
	) {
	}

	/**
	 * Returns the name to export the prop under,
	 * or `null` if it is not exported
	 */
	public function export(string $default): string|null
	{
		return match ($this->export) {
			false   => null,
			true    => $default,
			default => $this->export
		};
	}
}
