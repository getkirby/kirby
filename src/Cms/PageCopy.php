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
	public function __construct(
		public Page $copy,
		public bool $files = false,
		public bool $children = false
	) {
	}

	public function adapt(): Page
	{
		foreach ($this->languages() as $language) {
			$this->slug($language);
			$this->uuids($language);
		}

		return $this->copy;
	}

	public static function for(
		Page $copy,
		bool $files = false,
		bool $children = false
	): Page {
		$copy = new static($copy, $files, $children);
		return $copy->adapt();
	}

	public function languages(): iterable
	{
		$kirby = App::instance();

		return match ($kirby->multilang()) {
			true  => $kirby->languages(),
			false => [null]
		};
	}

	/**
	 * Adapts slug for copied pages
	 */
	public function slug(
		Language|null $language
	): void {
		// single lang setup
		if ($language === null) {
			return;
		}

		// don't remove slug from default language
		if ($language->isDefault() === true) {
			return;
		}

		if ($this->copy->translation($language)->exists() === true) {
			$this->copy = $this->copy->save(
				['slug' => null],
				$language->code()
			);
		}
	}

	/**
	 * Adapts UUIDs for copied pages,
	 * replacing the old UUID with a newly generated one
	 * for all newly generated pages and files
	 */
	public function uuids(
		Language|null $language
	): void {
		if (Uuids::enabled() === false) {
			return;
		}

		if ($language instanceof Language && $language->isDefault() === false) {
			return;
		}

		$this->copy = $this->copy->save(
			['uuid' => Uuid::generate()],
			$language?->code()
		);

		// regenerate UUIDs of page files
		if ($this->files) {
			foreach ($this->copy->files() as $file) {
				$file->save(
					['uuid' => Uuid::generate()],
					$language?->code()
				);
			}
		}

		// regenerate UUIDs of all page children
		if ($this->children) {
			foreach ($this->copy->index(true) as $child) {
				// always adapt files of subpages as they are
				// currently always copied; but don't adapt
				// children because we already operate on the index
				static::for($child, true);
			}
		}
	}
}
