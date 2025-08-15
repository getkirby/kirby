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
		string|array|null $scheme = null
	): Identifiable|null {
		// handle UUID shortcuts with a leading @
		if ($scheme !== null && str_starts_with($uuid, '@') === true) {
			$uuid = $scheme . '://' . substr($uuid, 1);
		}

		// look up model by UUID while prioritizing
		// $this collection when searching
		if ($uuid = Uuid::from($uuid, $scheme, $this)) {
			return $uuid->model();
		}

		return null;
	}
}
