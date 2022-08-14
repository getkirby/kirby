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
	 */
	public function findByUuid(
		string $uuid,
		string|null $schema = null
	): Identifiable|null {
		if (Uuid::is($uuid, $schema) === true) {
			return Uuid::for($uuid, $this)->resolve();
		}

		return null;
	}
}
