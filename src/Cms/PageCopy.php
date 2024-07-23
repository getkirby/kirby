<?php

namespace Kirby\Cms;

use Kirby\Uuid\Uuid;
use Kirby\Uuid\Uuids;

/**
 * Normalizes a newly generated copy of a page,
 * adapting page slugs, UUIDs etc.
 * (for single as well as multilang setups)
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.0.0
 * @internal
 */
class PageCopy extends Blueprint
{
	public static function adapt(
		Page $copy,
		bool $files = false,
		bool $children = false
	): Page {
		$kirby     = App::instance();
		$languages = match ($kirby->multilang()) {
			true  => $kirby->languages(),
			false => [null]
		};

		foreach ($languages as $language) {
			// replace UUIDs for newly generated pages and files
			$copy = static::uuids(
				$copy,
				$language,
				$files,
				$children
			);

			// remove all translated slugs
			$copy = static::slug($copy, $language);
		}

		return $copy;
	}

	/**
	 * Adapts slug for copied pages
	 */
	public static function slug(
		Page $copy,
		Language|null $language
	): Page
	{
		// single lang setup
		if ($language === null) {
			return $copy;
		}

		// don't remove slug from default language
		if ($language->isDefault() === true) {
			return $copy;
		}

		if ($copy->translation($language)->exists() === true) {
			$copy = $copy->save(['slug' => null], $language->code());
		}

		return $copy;
	}

	/**
	 * Adapts UUIDs for copied pages,
	 * replacing the old UUID with a newly generated one
	 * for all newly generated pages and files
	 */
	public static function uuids(
		Page $copy,
		Language|null $language,
		bool $files,
		bool $children
	): Page
	{
		if (Uuids::enabled() === false) {
			return $copy;
		}

		if ($language instanceof Language && $language->isDefault() === false) {
			return $copy;
		}

		$copy = $copy->save(
			['uuid' => Uuid::generate()],
			$language?->code()
		);

		// regenerate UUIDs of page files
		if ($files) {
			foreach ($copy->files() as $file) {
				$file->save(
					['uuid' => Uuid::generate()],
					$language?->code()
				);
			}
		}

		// regenerate UUIDs of all page children
		if ($children) {
			foreach ($copy->index(true) as $child) {
				// always adapt files of subpages as they are
				// currently always copied; but don't adapt
				// children because we already operate on the index
				static::adapt($child, true);
			}
		}

		return $copy;
	}
}
