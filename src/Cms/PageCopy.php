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
class PageCopy
{
	public function __construct(
		public Page $copy,
		public Page|null $original = null,
		public bool $files = false,
		public bool $children = false,
		public array $uuids = []
	) {
	}

	/**
	 * Returns the pages collection for children to adapt
	 */
	public function children(): Pages
	{
		return match($this->children) {
			true  => $this->copy->index(drafts: true),
			false => new Pages()
		};
	}

	/**
	 * Converts UUIDs for copied pages,
	 * replacing the old UUID with a newly generated one
	 * for all newly generated pages and files
	 */
	public function convertUuids(Language|null $language): void {
		if (Uuids::enabled() === false) {
			return;
		}

		if ($language instanceof Language && $language->isDefault() === false) {
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
		foreach ($this->children() as $child) {
			// always adapt files of subpages as they are
			// currently always copied; but don't adapt
			// children because we already operate on the index
			$child = new PageCopy($child, files: true, uuids: $this->uuids);
			$child->convertUuids($language);
			$this->uuids = [...$this->uuids, ...$child->uuids];
		}
	}

	/**
	 * Re-generate UUID for each file and track the change
	 */
	protected function convertFileUuids(Language|null $language): void
	{
		// if files have been copied,
		// re-generate UUIDs and track changes
		foreach ($this->files() as $file) {
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

		// if files have not been copied over,
		// track file UUIDs from original page to
		// remove/replace with empty string
		if ($this->files === false) {
			foreach ($this->original?->files() ?? [] as $file) {
				$this->uuids[$file->uuid()->toString()] = '';
			}
		}
	}

	/**
	 * Returns the files collection for files to adapt
	 */
	public function files(): Files
	{
		return match($this->files) {
			true  => $this->copy->files(),
			false => new Files()
		};
	}

	/**
	 * Returns all languages to adapt
	 *
	 * @todo Refactor once singe-lang mode also works with a language object
	 */
	public function languages(): Languages|iterable
	{
		$kirby = App::instance();

		return match ($kirby->multilang()) {
			true  => $kirby->languages(),
			false => [null]
		};
	}

	/**
	 * Processes the copy with all necessary adaptations.
	 * Main method to use if not familiar with individual steps.
	 */
	public static function process(
		Page $copy,
		Page $original,
		bool $files = false,
		bool $children = false
	): Page {
		$converter = new static($copy, $original, $files, $children);

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
	 * Removes translated slug for copied page
	 */
	public function removeSlug(Language|null $language): void {
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

		$this->copy->storage()->replace($this->uuids);

		foreach ($this->files() as $file) {
			$file->storage()->replace($this->uuids);
		}

		foreach ($this->children() as $child) {
			$child = new PageCopy($child, files: true, uuids: $this->uuids);
			$child->replaceUuids();
		}
	}
}
