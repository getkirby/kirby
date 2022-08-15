<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\App;
use Kirby\Cms\Collection;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Toolkit\A;

/**
 * Handles finding an object by its UUID from
 * inside the whole site through indexes
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Index
{
	/**
	 * Look up model by traversing step-by-step through first local context,
	 * then global index and stop as soon as matching UUID has been found.
	 * Not needed for site/users as they can be directly looked up.
	 */
	public static function find(Uuid $uuid): Identifiable|null
	{
		// lookup helper that first checks local context,
		// then global index by applying the provided lookup function
		$find = fn ($finder, $in) =>
			$finder($uuid->context() ?? []) ?? $finder($in);

		return match ($uuid->type()) {
			'page' => $find(
				fn ($in) => static::findInContent($in, $uuid->id()),
				static::pages()
			),
			'file' => $find(
				fn ($in) => static::findInContent($in, $uuid->id()),
				static::files()
			),
			// @codeCoverageIgnoreStart
			// TODO: this, once we know how UUID is applied to these objects
			'block'  => null,
			'struct' => null,
			// @codeCoverageIgnoreEnd
			default  => null
		};
	}

	/**
	 * Finds first model from generator collection
	 * which has a matching `uuid` content field value
	 */
	public static function findInContent(
		iterable $collection,
		string $id
	): ModelWithContent|null {
		foreach ($collection as $model) {
			if (Id::fromContent($model) === $id) {
				return $model;
			}
		}

		return null;
	}

	/**
	 * Populates cache with UUIDs for all identifiable models
	 * that need to be cached (not site and users)
	 */
	public static function populate(): void
	{
		// pages and page files
		foreach (static::pages() as $page) {
			$page->uuid()->populate();

			foreach ($page->files() as $file) {
				$file->uuid()->populate();
			}
		}

		$kirby = App::instance();

		// site files
		foreach ($kirby->site()->files() as $file) {
			$file->uuid()->populate();
		}

		// user files
		foreach ($kirby->users()->files() as $file) {
			$file->uuid()->populate();
		}

		// @codeCoverageIgnoreStart
		// blocks
		foreach (static::blocks() as $block) {
			$block->uuid()->populate();
		}

		// structure entries
		foreach (static::structures() as $structure) {
			$structure->uuid()->populate();
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Returns generator for all blocks in the site
	 * (in any page's, file's or user's content file)
	 *
	 * @return \Generator|\Kirby\Cms\Block[]
	 */
	public static function blocks(): Generator
	{
		foreach (static::fields('blocks') as $field) {
			yield from $field->toBlocks();
		}
	}

	/**
	 * Returns generator for all fields of type in the site
	 * (in any page's, file's or user's content file)
	 *
	 * @return \Generator|\Kirby\Cms\Field[]
	 */
	public static function fields(string $type): Generator
	{
		$fields = function (Generator|Collection $models) use ($type): Generator {
			foreach ($models as $model) {
				$fields = $model->blueprint()->fields();

				foreach ($fields as $name => $field) {
					// skip all fields, except fields of specified type
					if (A::get($field, 'type') !== $type) {
						continue;
					}

					yield $model->$name();
				}
			}
		};

		yield from $fields(static::pages());
		yield from $fields(static::files());
		yield from $fields(App::instance()->users());
	}

	/**
	 * Returns generator for all files in the site
	 * (of all pages, users and site)
	 *
	 * @return \Generator|\Kirby\Cms\File[]
	 */
	public static function files(): Generator
	{
		$kirby = App::instance();

		yield from $kirby->site()->files();

		foreach (static::pages() as $page) {
			yield from $page->files();
		}

		foreach ($kirby->users() as $user) {
			yield from $user->files();
		}
	}

	/**
	 * Returns generator for all pages and drafts in the site
	 *
	 * @return \Generator|\Kirby\Cms\Page[]
	 */
	public static function pages(Page|null $entry = null): Generator
	{
		$entry ??= App::instance()->site();

		foreach ($entry->childrenAndDrafts() as $page) {
			yield $page;
			yield from static::pages($page);
		}
	}

	/**
	 * Returns generator for all structure entries in the site
	 * (in any page's, file's or user's content file)
	 *
	 * @return \Generator|\Kirby\Cms\StructureObject[]
	 */
	public static function structures(): Generator
	{
		foreach (static::fields('structure') as $field) {
			yield from $field->toStructure();
		}
	}
}
