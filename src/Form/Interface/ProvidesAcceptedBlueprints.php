<?php

namespace Kirby\Form\Interface;

/**
 * Implemented by fields that list models of a parent
 * and can tell which blueprints those models may use
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
interface ProvidesAcceptedBlueprints
{
	/**
	 * Returns all blueprints that are available for the
	 * models in this field
	 */
	public function blueprints(): array;
}
