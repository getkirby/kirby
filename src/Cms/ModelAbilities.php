<?php

namespace Kirby\Cms;

/**
 * Abilities for a model object
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class ModelAbilities
{
	/**
	 * Checks if there's a dedicated check method
	 * for the given action
	 */
	public function has(string $action): bool
	{
		return match ($action) {
			'has'   => false,
			default => method_exists($this, $action)
		};
	}
}
