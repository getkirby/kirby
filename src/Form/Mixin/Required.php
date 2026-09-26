<?php

namespace Kirby\Form\Mixin;

/**
 * Provides the `required` prop to mark the field as mandatory
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait Required
{
	/**
	 * If `true`, the field has to be filled in correctly to be saved.
	 */
	protected bool $required = false;

	/**
	 * Checks if the field is required
	 */
	public function isRequired(): bool
	{
		return $this->required();
	}

	public function required(): bool
	{
		return $this->required;
	}
}
