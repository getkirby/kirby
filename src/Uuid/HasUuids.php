<?php

namespace Kirby\Uuid;

/**
 * Adds UUID lookup to collections
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait HasUuids
{
	/**
	 * Find a single element by global UUID
	 * @since 3.8.0
	 */
	protected function findByUuid(
		string $uuid,
		string|null $scheme = null
	): Identifiable|null {
		if (Uuid::is($uuid, $scheme) === true) {
			// look up model by UUID while prioritizing
			// $this collection when searching
			return Uuid::for($uuid, $this)->model();
		}

		return null;
	}
}
