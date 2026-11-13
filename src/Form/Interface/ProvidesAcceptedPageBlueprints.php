<?php

namespace Kirby\Form\Interface;

/**
 * Implemented by fields that can add pages
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
}
