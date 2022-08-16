<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use Kirby\Cms\Page;

/**
 * Uuid for \Kirby\Cms\File
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
	public Identifiable|null $model;

	/**
	 * Look up Uuid in cache and resolve
	 * to page object
	 */
	protected function findByCache(): Page|null
	{
		$key   = $this->key();
		$value = Uuids::cache()->get($key);
		return App::instance()->page($value);
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
}
