<?php

namespace Kirby\Cms;

use Kirby\Filesystem\Dir;
use Kirby\Toolkit\Str;

/**
 * HasChildren
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait HasChildren
{
	/**
	 * The list of available published children
	 */
	public Pages|null $children = null;

	/**
	 * The list of available draft children
	 */
	public Pages|null $drafts = null;

	/**
	 * The combined list of available published
	 * and draft children
	 */
	public Pages|null $childrenAndDrafts = null;

	/**
	 * Returns all published children
	 */
	public function children(): Pages
	{
		return $this->children ??= Pages::factory($this->inventory()['children'], $this);
	}

	/**
	 * Returns all published and draft children at the same time
	 */
	public function childrenAndDrafts(): Pages
	{
		return $this->childrenAndDrafts ??= $this->children()->merge($this->drafts());
	}

	/**
	 * Searches for a draft child by ID
	 */
	public function draft(string $path): Page|null
	{
		$path = str_replace('_drafts/', '', $path);

		if (Str::contains($path, '/') === false) {
			return $this->drafts()->find($path);
		}

		$parts  = explode('/', $path);
		$parent = $this;

		foreach ($parts as $slug) {
			if ($page = $parent->find($slug)) {
				$parent = $page;
				continue;
			}

			if ($draft = $parent->drafts()->find($slug)) {
				$parent = $draft;
				continue;
			}

			return null;
		}

		return $parent;
	}

	/**
	 * Returns all draft children
	 */
	public function drafts(): Pages
	{
		if ($this->drafts instanceof Pages) {
			return $this->drafts;
		}

		$kirby = $this->kirby();

		// create the inventory for all drafts
		$inventory = Dir::inventory(
			$this->root() . '/_drafts',
			$kirby->contentExtension(),
			$kirby->contentIgnore(),
			$kirby->multilang()
		);

		return $this->drafts = Pages::factory($inventory['children'], $this, true);
	}

	/**
	 * Finds one or multiple published children by ID
	 */
	public function find(string|array ...$arguments): Page|Pages|null
	{
		return $this->children()->find(...$arguments);
	}

	/**
	 * Finds a single published or draft child
	 */
	public function findPageOrDraft(string $path): Page|null
	{
		return $this->children()->find($path) ?? $this->drafts()->find($path);
	}

	/**
	 * Returns a collection of all published children of published children
	 */
	public function grandChildren(): Pages
	{
		return $this->children()->children();
	}

	/**
	 * Checks if the model has any published children
	 */
	public function hasChildren(): bool
	{
		return $this->children()->count() > 0;
	}

	/**
	 * Checks if the model has any draft children
	 */
	public function hasDrafts(): bool
	{
		return $this->drafts()->count() > 0;
	}

	/**
	 * Checks if the page has any listed children
	 */
	public function hasListedChildren(): bool
	{
		return $this->children()->listed()->count() > 0;
	}

	/**
	 * Checks if the page has any unlisted children
	 */
	public function hasUnlistedChildren(): bool
	{
		return $this->children()->unlisted()->count() > 0;
	}

	/**
	 * Creates a flat child index
	 *
	 * @param bool $drafts If set to `true`, draft children are included
	 */
	public function index(bool $drafts = false): Pages
	{
		if ($drafts === true) {
			return $this->childrenAndDrafts()->index($drafts);
		}

		return $this->children()->index();
	}

	/**
	 * Sets the published children collection
	 *
	 * @return $this
	 */
	protected function setChildren(array $children = null): static
	{
		if ($children !== null) {
			$this->children = Pages::factory($children, $this);
		}

		return $this;
	}

	/**
	 * Sets the draft children collection
	 *
	 * @return $this
	 */
	protected function setDrafts(array $drafts = null): static
	{
		if ($drafts !== null) {
			$this->drafts = Pages::factory($drafts, $this, true);
		}

		return $this;
	}
}
