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
 * @unstable
 */
class PageCopy
{
	public function __construct(
		public Page $copy,
		public Page|null $original = null,
		public bool $withFiles = false,
		public bool $withChildren = false,
		public array $uuids = []
	) {
	}

	/**
	 * Converts UUIDs for copied pages,
	 * replacing the old UUID with a newly generated one
	 * for all newly generated pages and files
	 */
	public function convertUuids(Language|null $language): void
	{
		if (Uuids::enabled() === false) {
			return;
		}

		if (
			$language instanceof Language &&
			$language->isDefault() === false
		) {
			return;
		}

		// store old UUID
		$old = $this->copy->uuid()->toString();

		// re-generate UUID for the page
		$this->copy = $this->copy->save(
			['uuid' => Uuid::generate()],
			$language?->code()
		);

		// track UUID change
		$this->uuids[$old] = $this->copy->uuid()->toString();

		$this->convertFileUuids($language);
		$this->convertChildrenUuids($language);
	}

	/**
	 * Re-generate UUIDs for each child recursively
	 * and merge with the tracked changed UUIDs
	 */
	protected function convertChildrenUuids(Language|null $language): void
	{
		// re-generate UUIDs and track changes
		if ($this->withChildren === true) {
			foreach ($this->copy->childrenAndDrafts() as $child) {
				// always adapt files of subpages as they are
				// currently always copied; adapt children recursively
				$child = new PageCopy(
					$child,
					withChildren: true,
					withFiles: true,
					uuids: $this->uuids
				);
				$child->convertUuids($language);
				$this->uuids = [...$this->uuids, ...$child->uuids];
			}
		}

		// if children have not been copied over,
		// track all children UUIDs from original page to
		// remove/replace with empty string
		if ($this->withChildren === false) {
			foreach ($this->original?->index(drafts: true) ?? [] as $child) {
				$this->uuids[$child->uuid()->toString()] = '';

				foreach ($child->files() as $file) {
					$this->uuids[$file->uuid()->toString()] = '';
				}
			}
		}
	}

	/**
	 * Re-generate UUID for each file and track the change
	 */
	protected function convertFileUuids(Language|null $language): void
	{
		// re-generate UUIDs and track changes
		if ($this->withFiles === true) {
			foreach ($this->copy->files() as $file) {
				// store old file UUID
				$old = $file->uuid()->toString();

				// re-generate UUID for the file
				$file = $file->save(
					['uuid' => Uuid::generate()],
					$language?->code()
				);

				// track UUID change
				$this->uuids[$old] = $file->uuid()->toString();
			}
		}

		// if files have not been copied over,
		// track file UUIDs from original page to
		// remove/replace with empty string
		if ($this->withFiles === false) {
			foreach ($this->original?->files() ?? [] as $file) {
				$this->uuids[$file->uuid()->toString()] = '';
			}
		}
	}

	/**
	 * Returns all languages to adapt
	 *
	 * @todo Refactor once singe-lang mode also works with a language object
	 */
	public function languages(): Languages|iterable
	{
		$kirby = App::instance();

		if ($kirby->multilang() === true) {
			return $kirby->languages();
		}

		return [null];
	}

	/**
	 * Processes the copy with all necessary adaptations.
	 * Main method to use if not familiar with individual steps.
	 */
	public static function process(
		Page $copy,
		Page|null $original = null,
		bool $withFiles = false,
		bool $withChildren = false
	): Page {
		$converter = new static($copy, $original, $withFiles, $withChildren);

		// loop through all languages to remove slug from non-default
		// languages and re-generate UUIDs (and track changes)
		foreach ($converter->languages() as $language) {
			$converter->removeSlug($language);
			$converter->convertUuids($language);
		}

		// apply all tracked UUID changes at once
		$converter->replaceUuids();

		return $converter->copy;
	}

	/**
	 * Removes translated slug for copied page.
	 * This is needed to avoid translated slug
	 * collisions with the original page.
	 */
	public function removeSlug(Language|null $language): void
	{
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
	 * Replace old UUIDs with new UUIDs in the content
	 */
	public function replaceUuids(): void
	{
		if (Uuids::enabled() === false) {
			return;
		}

		foreach ($this->copy->storage()->all() as $versionId => $language) {
			$this->copy->storage()->replaceStrings(
				$versionId,
				$language,
				$this->uuids
			);
		}

		if ($this->withFiles === true) {
			foreach ($this->copy->files() as $file) {
				foreach ($file->storage()->all() as $versionId => $language) {
					$file->storage()->replaceStrings(
						$versionId,
						$language,
						$this->uuids
					);
				}
			}
		}

		if ($this->withChildren === true) {
			foreach ($this->copy->childrenAndDrafts() as $child) {
				$child = new PageCopy($child, withFiles: true, withChildren: true, uuids: $this->uuids);
				$child->replaceUuids();
			}
		}
	}
}
