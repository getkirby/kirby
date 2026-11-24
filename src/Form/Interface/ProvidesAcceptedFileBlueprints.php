<?php

namespace Kirby\Form\Interface;

/**
 * Implemented by fields that can add files to their
 * model and tell which blueprints those files may use
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
interface ProvidesAcceptedFileBlueprints
{
	/**
	 * Returns the names of the blueprints that files
	 * of the field's model may use through this field
	 *
	 * @return list<string>
	 */
	public function acceptedFileBlueprints(): array;
}
