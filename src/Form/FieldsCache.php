<?php

namespace Kirby\Form;

use Closure;

/**
 * Store for values that are expensive to create and can be
 * shared between all fields of a form and its nested forms.
 *
 * One instance is created per `Kirby\Form\Fields` collection
 * and passed on to all nested forms, so that the work is only
 * done once per form tree.
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class FieldsCache
{
	protected array $values = [];

	public function getOrSet(string $key, Closure $result): mixed
	{
		if (array_key_exists($key, $this->values) === false) {
			$this->values[$key] = $result();
		}

		return $this->values[$key];
	}
}
