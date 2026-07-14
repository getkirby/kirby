<?php

namespace Kirby\Cms;

/**
 * Model Abilities
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class ModelAbilities
{
	public function __call(string $name, array $args = []): bool
	{
		return true;
	}
}
