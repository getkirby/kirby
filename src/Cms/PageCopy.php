<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Uuid\Identifiable;
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
		public bool $children = false,
		public array $uuids = []
	) {
	}

	/**
	 * Applies various adaptions to the copied pages, files and children
	 */
	public function adapt(): void
	{
		foreach ($this->languages() as $language) {
			$this->adaptSlug($language);
			$this->adaptUuids($language);
		}
	}

	/**
	 * Adapts slug for copied pages
	 */
	public function adaptSlug(
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
	public function adaptUuids(
		Language|null $language
	): void {
		if (Uuids::enabled() === false) {
			return;
		}

		if ($language instanceof Language && $language->isDefault() === false) {
			return;
		}

		// re-generate UUID for page and track the change
		$this->trackUuid(
			$this->copy,
			fn () => $this->copy = $this->copy->save(
				['uuid' => Uuid::generate()],
				$language?->code()
			)
		);

		// re-generate UUID for each file and track the change
		foreach ($this->files() as $file) {
			$this->trackUuid(
				$file,
				fn () => $file->save(
					['uuid' => Uuid::generate()],
					$language?->code()
				)
			);
		}

		// re-generate UUIDs for each child recursively
		// and merge with the tracked changed UUIDs
		foreach ($this->children() as $child) {
			// always adapt files of subpages as they are
			// currently always copied; but don't adapt
			// children because we already operate on the index
			$child = new PageCopy($child, files: true, uuids: $this->uuids);
			$child->adapt();
			$this->uuids = [...$this->uuids, ...$child->uuids];
		}
	}

	/**
	 * Applies adaptations to the content of everything copied
	 */
	public function apply(): void
	{
		$this->applyUuids();
	}

	/**
	 * Replace old UUIDs with new UUIDs in the content
	 */
	public function applyUuids(): void
	{
		$search  = array_keys($this->uuids);
		$replace = array_values($this->uuids);

		$this->copy->storage()->replace($search, $replace);

		foreach ($this->files() as $file) {
			$file->storage()->replace($search, $replace);
		}

		foreach ($this->children() as $child) {
			$child = new PageCopy($child, files: true, uuids: $this->uuids);
			$child->replace();
		}
	}

	public function children(): Pages
	{
		return $this->children ? $this->copy->index(true) : new Pages();
	}

	public function files(): Files
	{
		return $this->files ? $this->copy->files() : new Files();
	}

	public static function for(
		Page $copy,
		bool $files = false,
		bool $children = false
	): Page {
		$copy = new static($copy, $files, $children);
		$copy->adapt();
		$copy->apply();
		return $copy->result();
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
	 * Returns the copied page after all adaptations
	 * @codeCoverageIgnore
	 */
	public function result(): Page
	{
		return $this->copy;
	}

	/**
	 * Executes the action while adding the
	 * old and new UUID to the $uuids map
	 */
	public function trackUuid(
		Identifiable $model,
		Closure $action
	): void {
		$old               = $model->uuid()->toString();
		$model             = $action();
		$this->uuids[$old] = $model->uuid()->toString();
	}
}
