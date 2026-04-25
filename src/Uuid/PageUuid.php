<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use Kirby\Cms\Page;

/**
 * UUID for \Kirby\Cms\Page
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.8.0
 *
 * @method \Kirby\Cms\Page|null model(bool $lazy = false)
 */
class PageUuid extends ModelUuid
{
	protected const string TYPE = 'page';

	/**
	 * @var \Kirby\Cms\Page|null
	 */
	public Identifiable|null $model = null;

	/**
	 * Removes the current UUID from cache,
	 * recursively including all children if needed
	 */
	public function clear(bool $recursive = false): bool
	{
		/**
		 * If $recursive, also clear UUIDs from cache for all children
		 * @var \Kirby\Cms\Page $model
		 */
		if ($recursive === true && $model = $this->model()) {
			foreach ($model->children() as $child) {
				$child->uuid()->clear(true);
			}
		}

		return parent::clear();
	}

	/**
	 * Looks up UUID in cache and resolves
	 * to page object
	 */
	protected function findByCache(): Page|null
	{
		if ($key = $this->key()) {
			if ($value = Uuids::cache()->get($key)) {
				return App::instance()->page($value);
			}
		}

		return null;
	}

	/**
	 * Generator for all pages and drafts in the site
	 *
	 * @return \Generator|\Kirby\Cms\Page[]
	 */
	public static function index(Page|null $entry = null): Generator
	{
		$entry ??= App::instance()->site();

		foreach ($entry->childrenAndDrafts() as $page) {
			yield $page;
			yield from static::index($page);
		}
	}

	/**
	 * Feeds the UUID for the page (and optionally
	 * its children) into the cache
	 */
	public function populate(
		bool $force = false,
		bool $recursive = false
	): bool {
		/**
		 * If $recursive, also populate UUIDs for all children
		 * @var \Kirby\Cms\Page $model
		 */
		if ($recursive === true && $model = $this->model()) {
			foreach ($model->children() as $child) {
				$child->uuid()->populate($force, true);
			}
		}

		return parent::populate($force);
	}

	/**
	 * Returns permalink url
	 */
	public function toPermalink(): string
	{
		return (new Permalink($this))->url();
	}
}
