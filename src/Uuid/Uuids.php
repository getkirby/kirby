<?php

namespace Kirby\Uuid;

use Kirby\Cache\Cache;
use Kirby\Cms\App;

/**
 * Helper methods that deal with the entirety of UUIDs in the system
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Uuids
{
	/**
	 * Returns the instance for the lookup cache
	 */
	public static function cache(): Cache
	{
		return App::instance()->cache('uuid');
	}

	/**
	 * Populates cache with UUIDs for all identifiable models
	 * that need to be cached (not site and users)
	 *
	 * @param string $type which models to include (`all`|`page`|`file`|`block`|`struct`)
	 */
	public static function populate(string $type = 'all'): void
	{
		if ($type === 'all' || $type === 'page' || $type === 'file') {
			foreach (PageUuid::index() as $page) {
				if ($type === 'all' || $type === 'page') {
					Uuid::for($page)->populate();
				}

				if ($type === 'all' || $type === 'file') {
					foreach ($page->files() as $file) {
						Uuid::for($file)->populate();
					}
				}
			}
		}

		if ($type === 'all' || $type === 'file') {
			foreach (SiteUuid::index() as $site) {
				foreach ($site->files() as $file) {
					Uuid::for($file)->populate();
				}
			}

			foreach (UserUuid::index() as $user) {
				foreach ($user->files() as $file) {
					Uuid::for($file)->populate();
				}
			}
		}

		// TODO: activate for uuid-block-structure-support
		// if ($type === 'all' || $type === 'block') {
		// 	foreach (BlockUuid::index() as $blocks) {
		// 		foreach ($blocks as $block) {
		// 			Uuid::for($block)->populate();
		// 		}
		// 	}
		// }

		// if ($type === 'all' || $type === 'struct') {
		// 	foreach (StructureUuid::index() as $structure) {
		// 		foreach ($structure as $entry) {
		// 			Uuid::for($entry)->populate();
		// 		}
		// 	}
		// }
	}
}
