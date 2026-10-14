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
 * `exportAs` exports the prop under a different name.
 * `getter` names the method that returns the exported value,
 * if it differs from the property name.
 *
 * `computed` and `default` are only used for the documentation.
 *
 * The attribute is inherited when a child class redeclares
 * the property, e.g. to change its default value. To change
 * how a value gets stored, add a `set{Name}()` method.
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
		public bool $export = true,
		public string|null $exportAs = null,
		public string|null $getter = null
	) {
	}
}
