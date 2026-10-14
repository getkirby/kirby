<?php

namespace Kirby\Reflection\Attributes;

use Attribute;

/**
 * Marks a property that can be set via props,
 * e.g. from a blueprint or the constructor.
 *
 * `computed: true` marks a prop whose final value
 * is computed at runtime. `default` documents a
 * default value that the property itself cannot hold.
 * `export: false` excludes the prop from the props
 * that are exported by `Kirby\Reflection\Props::values()`.
 * `export: 'name'` exports the prop under a different name.
 * `getter` names the method that returns the exported value,
 * if it differs from the property name.
 * `setter` names the method that receives the value when the
 * prop is set, e.g. to normalize it before it gets stored.
 *
 * `computed` and `default` are only used for the documentation.
 *
 * The attribute is inherited when a child class redeclares
 * the property, e.g. to change its default value.
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
	 * Returns the name under which the prop is exported
	 * or `null` if the prop is not exported
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
