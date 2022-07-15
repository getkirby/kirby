<?php

namespace Kirby\Cms;

/**
 * Foundation for Pages, Files and Users models.
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class Models extends Collection
{
	/**
	 * Find a single element by global UUID
	 *
	 * @param string $uuid
	 * @param string|null $schema
	 * @return \Kirby\Cms\Page|\Kirby\Cms\File|\Kirby\Cms\User|\Kirby\Cms\Site|null
	 */
	public function findByUuid(string $uuid, string|null $schema): Page|File|User|Site|null
	{
		if (Uuid::is($uuid, $schema) === true) {
			return Uuid::for($uuid, $this)->toModel();
		}

		return null;
	}
}
