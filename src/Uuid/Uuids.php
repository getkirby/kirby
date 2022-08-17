<?php

namespace Kirby\Uuid;

use Kirby\Cache\Cache;
use Kirby\Cms\App;

/**
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Uuids
{
	/**
	 * Get instance for lookup cache
	 */
	public static function cache(): Cache
	{
		return App::instance()->cache('uuid');
	}

	/**
	 * Populates cache with Uuids for all identifiable models
	 * that need to be cached (not site and users)
	 */
	public static function populate(string $type = 'all'): void
	{
		if ($type === 'all' || $type === 'pages' || $type === 'files') {
			foreach (PageUuid::index() as $page) {
				if ($type === 'all' || $type === 'pages') {
					Uuid::for($page)->populate();
				}

				if ($type === 'all' || $type === 'files') {
					foreach ($page->files() as $file) {
						Uuid::for($file)->populate();
					}
				}
			}
		}

		if ($type === 'all' || $type === 'files') {
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
		// if ($type === 'all' || $type === 'blocks') {
		// 	foreach (BlockUuid::index() as $blocks) {
		// 		foreach ($blocks as $block) {
		// 			Uuid::for($block)->populate();
		// 		}
		// 	}
		// }

		// if ($type === 'all' || $type === 'structures') {
		// 	foreach (StructureUuid::index() as $structure) {
		// 		foreach ($structure as $entry) {
		// 			Uuid::for($entry)->populate();
		// 		}
		// 	}
		// }
	}
}
