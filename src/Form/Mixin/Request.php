<?php

namespace Kirby\Form\Mixin;

/**
 * Provides access to request parameters that belong to a single field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Request
{
	/**
	 * Reads a request parameter from the field's own scope, so that
	 * fields on the same view never overwrite each other's parameters.
	 *
	 * Field names are unique per model, so the name addresses the field
	 * well enough. Nested fields will need their full `a+b+c` path here,
	 * the same key the field API is addressed with.
	 *
	 * @example ?fields[gallery][page]=2&fields[cover][page]=3
	 */
	public function request(string $key, mixed $fallback = null): mixed
	{
		return $this->kirby()->request()->get(
			'fields.' . $this->name() . '.' . $key,
			$fallback
		);
	}
}
