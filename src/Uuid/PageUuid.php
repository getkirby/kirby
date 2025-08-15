<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use Kirby\Cms\Page;

/**
 * UUID for \Kirby\Cms\Page
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
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
}
