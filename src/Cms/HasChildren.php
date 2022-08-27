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
	 *
	 * @var \Kirby\Cms\Pages|null
	 */
	public $children;

	/**
	 * The list of available draft children
	 *
	 * @var \Kirby\Cms\Pages|null
	 */
	public $drafts;

	/**
	 * The combined list of available published
	 * and draft children
	 *
	 * @var \Kirby\Cms\Pages|null
	 */
	public $childrenAndDrafts;

	/**
	 * Returns all published children
	 *
	 * @return \Kirby\Cms\Pages
	 */
	public function children()
	{
		if ($this->children instanceof Pages) {
			return $this->children;
		}

		return $this->children = Pages::factory($this->inventory()['children'], $this);
	}

	/**
	 * Returns all published and draft children at the same time
	 *
	 * @return \Kirby\Cms\Pages
	 */
	public function childrenAndDrafts()
	{
		if ($this->childrenAndDrafts instanceof Pages) {
			return $this->childrenAndDrafts;
		}

		return $this->childrenAndDrafts = $this->children()->merge($this->drafts());
	}

	/**
	 * Returns a list of IDs for the model's
	 * `toArray` method
	 *
	 * @return array
	 */
	protected function convertChildrenToArray(): array
	{
		return $this->children()->keys();
	}

	/**
	 * Searches for a draft child by ID
	 *
	 * @param string $path
	 * @return \Kirby\Cms\Page|null
	 */
	public function draft(string $path)
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
	 *
	 * @return \Kirby\Cms\Pages
	 */
	public function drafts()
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
	 *
	 * @param string ...$arguments
	 * @return \Kirby\Cms\Page|\Kirby\Cms\Pages|null
	 */
	public function find(...$arguments)
	{
		return $this->children()->find(...$arguments);
	}

	/**
	 * Finds a single published or draft child
	 *
	 * @param string $path
	 * @return \Kirby\Cms\Page|null
	 */
	public function findPageOrDraft(string $path)
	{
		return $this->children()->find($path) ?? $this->drafts()->find($path);
	}

	/**
	 * Returns a collection of all published children of published children
	 *
	 * @return \Kirby\Cms\Pages
	 */
	public function grandChildren()
	{
		return $this->children()->children();
	}

	/**
	 * Checks if the model has any published children
	 *
	 * @return bool
	 */
	public function hasChildren(): bool
	{
		return $this->children()->count() > 0;
	}

	/**
	 * Checks if the model has any draft children
	 *
	 * @return bool
	 */
	public function hasDrafts(): bool
	{
		return $this->drafts()->count() > 0;
	}

	/**
	 * Checks if the page has any listed children
	 *
	 * @return bool
	 */
	public function hasListedChildren(): bool
	{
		return $this->children()->listed()->count() > 0;
	}

	/**
	 * Checks if the page has any unlisted children
	 *
	 * @return bool
	 */
	public function hasUnlistedChildren(): bool
	{
		return $this->children()->unlisted()->count() > 0;
	}

	/**
	 * Creates a flat child index
	 *
	 * @param bool $drafts If set to `true`, draft children are included
	 * @return \Kirby\Cms\Pages
	 */
	public function index(bool $drafts = false)
	{
		if ($drafts === true) {
			return $this->childrenAndDrafts()->index($drafts);
		}

		return $this->children()->index();
	}

	/**
	 * Sets the published children collection
	 *
	 * @param array|null $children
	 * @return $this
	 */
	protected function setChildren(array $children = null)
	{
		if ($children !== null) {
			$this->children = Pages::factory($children, $this);
		}

		return $this;
	}

	/**
	 * Sets the draft children collection
	 *
	 * @param array|null $drafts
	 * @return $this
	 */
	protected function setDrafts(array $drafts = null)
	{
		if ($drafts !== null) {
			$this->drafts = Pages::factory($drafts, $this, true);
		}

		return $this;
	}
}
