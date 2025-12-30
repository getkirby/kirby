<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use Kirby\Cms\Page;

/**
 * UUID for \Kirby\Cms\Page
 * @since 3.8.0
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PageUuid extends ModelUuid
{
	protected const TYPE = 'page';

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
		// make sure UUID is cached because the permalink
		// route only looks up UUIDs from cache
		if ($this->isCached() === false) {
			$this->populate();
		}

		$kirby = App::instance();
		$url   = $kirby->url();

		if ($language = $kirby->language('current')) {
			$url = $language->url();
		}

		return $url . '/@/' . static::TYPE . '/' . $this->id();
	}

	/**
	 * @deprecated 5.1.0 Use `::toPermalink()` instead
	 */
	public function url(): string
	{
		return $this->toPermalink();
	}
}
