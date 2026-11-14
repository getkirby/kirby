<?php

namespace Kirby\Form\Interface;

/**
 * Implemented by fields that add or list pages
 * and tell which blueprints those pages may use
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
interface ProvidesAcceptedPageBlueprints
{
	/**
	 * Returns the names of the blueprints that
	 * pages added through this field may use
	 *
	 * @return list<string>
	 */
	public function acceptedPageBlueprints(): array;

	/**
	 * Whether the field lists child pages of its model,
	 * optionally only with the given blueprint
	 */
	public function hasPageBlueprint(string|null $blueprint = null): bool;
}
