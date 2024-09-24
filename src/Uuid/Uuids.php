<?php

namespace Kirby\Uuid;

use Closure;
use Kirby\Cache\Cache;
use Kirby\Cms\App;
use Kirby\Exception\LogicException;

/**
 * Helper methods that deal with the entirety of UUIDs in the system
 * @since 3.8.0
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
	 * Runs the callback for each identifiable model of type
	 *
	 * @param string $type which models to include (`all`|`page`|`file`|`block`|`struct`)
	 */
	public static function each(Closure $callback, string $type = 'all'): void
	{
		if ($type === 'all' || $type === 'page' || $type === 'file') {
			foreach (PageUuid::index() as $page) {
				if ($type === 'all' || $type === 'page') {
					$callback($page);
				}

				if ($type === 'all' || $type === 'file') {
					foreach ($page->files() as $file) {
						$callback($file);
					}
				}
			}
		}

		if ($type === 'all' || $type === 'file') {
			foreach (SiteUuid::index() as $site) {
				foreach ($site->files() as $file) {
					$callback($file);
				}
			}

			foreach (UserUuid::index() as $user) {
				foreach ($user->files() as $file) {
					$callback($file);
				}
			}
		}

		// TODO: activate for uuid-block-structure-support
		// if ($type === 'all' || $type === 'block') {
		// 	foreach (BlockUuid::index() as $blocks) {
		// 		foreach ($blocks as $block) {
		// 			$callback($block);
		// 		}
		// 	}
		// }

		// if ($type === 'all' || $type === 'struct') {
		// 	foreach (StructureUuid::index() as $structure) {
		// 		foreach ($structure as $entry) {
		// 			$callback($entry);
		// 		}
		// 	}
		// }
	}

	public static function enabled(): bool
	{
		return App::instance()->option('content.uuid') !== false;
	}

	/**
	 * Generates UUID for all identifiable models of type
	 *
	 * @param string $type which models to include (`all`|`page`|`file`|`block`|`struct`)
	 */
	public static function generate(string $type = 'all'): void
	{
		if (static::enabled() === false) {
			throw new LogicException(
				message: 'UUIDs have been disabled via the `content.uuid` config option.'
			);
		}

		static::each(
			fn (Identifiable $model) => Uuid::for($model)->id(),
			$type
		);
	}

	/**
	 * Populates cache with UUIDs for all identifiable models
	 * that need to be cached (not site and users)
	 *
	 * @param string $type which models to include (`all`|`page`|`file`|`block`|`struct`)
	 */
	public static function populate(string $type = 'all'): void
	{
		if (static::enabled() === false) {
			throw new LogicException(
				message: 'UUIDs have been disabled via the `content.uuid` config option.'
			);
		}

		static::each(
			fn (Identifiable $model) => Uuid::for($model)->populate(),
			$type
		);
	}
}
