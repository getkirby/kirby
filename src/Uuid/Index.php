<?php

namespace Kirby\Uuid;

use Kirby\Cms\App;
use Kirby\Cms\Blocks;
use Kirby\Cms\Collection;
use Kirby\Cms\Files;
use Kirby\Cms\Pages;
use Kirby\Cms\Structure;
use Kirby\Cms\Users;

/**
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Index
{
	/**
	 * Returns global index collection for traversing content files.
	 * Not needed for site/users as they can be directly looked
	 * up via ID without need to search through index.
	 */
	public static function collection(Uuid $uuid): Collection
	{
		$collection = match ($uuid->type()) {
			'page'  	=> static::pages(),
			'file'  	=> static::files(),
			/** @codeCoverageIgnoreStart */
			'block' 	=> static::blocks(),
			'structure' => static::structures()
			/** @codeCoverageIgnoreEnd */
		};

		// if a current collection was passed, remove it from
		// the global index as we have already checked it separately
		if ($context = $uuid->context()) {
			$collection = $collection->not($context);
		}

		return $collection;
	}

	/**
	 * Look up model by traversing through local context/global
	 * index and finding the matching UUID in content file.
	 * Not needed for site/users as they can be directly looked up.
	 * @todo make more performant
	 */
	public static function find(Uuid $uuid): Identifiable|null
	{
		$id   = $uuid->id();
		$type = $uuid->type();

		// lookup helper that first checks local context,
		// then global index by applying the provided lookup function
		$get = fn ($find) => $find($uuid->context()) ??
							 $find(static::collection($uuid));

		// TODO: does this work?
		// TODO: structure?
		/** @codeCoverageIgnoreStart */
		if ($type === 'block') {
			return $get(fn ($models) => $models?->get($id));
		}
		/** @codeCoverageIgnoreEnd */
		if ($type === 'page' || $type === 'file') {
			return $get(
				fn ($models) => $models?->filter(
					fn ($model) => $model->content()->get('uuid')->value() === $id
				)->first()
			);
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
			Uuid::for($page)->populate();

			foreach ($page->files() as $file) {
				Uuid::for($file)->populate();
			}
		}

		// site files
		foreach (App::instance()->site()->files() as $file) {
			Uuid::for($file)->populate();
		}

		// user files
		foreach (static::users()->files() as $file) {
			Uuid::for($file)->populate();
		}

		/** @codeCoverageIgnoreStart */
		// blocks
		foreach (static::blocks() as $block) {
			Uuid::for($block)->populate();
		}

		// structure entries
		foreach (static::structures() as $structure) {
			Uuid::for($structure)->populate();
		}
		/** @codeCoverageIgnoreEnd */
	}

	/**
	 * Returns collection of all blocks in the site
	 * @todo implement this
	 */
	public static function blocks(): Blocks
	{
		return new Blocks();
	}

	/**
	 * Returns collection of all files in the site
	 * @todo make more performant
	 */
	public static function files(): Files
	{
		$site  = App::instance()->site()->files();
		$pages = static::pages()->files();
		$users = static::users()->files();

		return $site->add($pages)->add($users);
	}

	/**
	 * Returns collection of all pages (incl. drafts) in the site
	 * @todo make more performant
	 */
	public static function pages(): Pages
	{
		return App::instance()->site()->index(true);
	}

	/**
	 * Returns collection of all structure entries in the site
	 * @todo implement this
	 */
	public static function structures(): Structure
	{
		return new Structure();
	}

	/**
	 * Returns collection of all users
	 * @todo make more performant
	 */
	public static function users(): Users
	{
		return App::instance()->users();
	}
}
