<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\File;

/**
 * Uuid for \Kirby\Cms\File
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class FileUuid extends ModelUuid
{
	protected const TYPE = 'file';

	/**
	 * @var \Kirby\Cms\File|null
	 */
	public Identifiable|null $model;

	/**
	 * Look up Uuid in cache and resolve to file object.
	 * Special for  FileUuid as the value stored in cache
	 * is a hybrid Uri from parent Uuid and filename. Needs
	 * to resolve parent Uuid and then get file by filename.
	 */
	protected function findByCache(): File|null
	{
		// get mixed Uri from cache
		$key   = $this->key();
		$value = Uuids::cache()->get($key);

		if ($value === null) {
			return null;
		}

		// value is itself another Uuid\Uri string
		// e.g. page://page-uuid/filename.jpg
		$uuid = new Uri($value);

		// we need to resolve the parent UUID to its model
		// and then query for the file by filename
		$parent   = Uuid::for($uuid->base())->resolve();
		$filename = $uuid->path()->toString();

		return $parent->file($filename);
	}

	/**
	 * Generator for all files in the site
	 * (of all pages, users and site)
	 *
	 * @return \Generator|\Kirby\Cms\File[]
	 */
	public static function index(): Generator
	{
		foreach (SiteUuid::index() as $site) {
			yield from $site->files();
		}

		foreach (PageUuid::index() as $page) {
			yield from $page->files();
		}

		foreach (UserUuid::index() as $user) {
			yield from $user->files();
		}
	}

	/**
	 * Returns value to be stored in cache
	 */
	public function value(): string
	{
		$model  = $this->resolve();
		$parent = Uuid::for($model->parent());

		// populate parent to cache itself as we'll need it
		// as well when resolving model later on
		$parent->populate();

		return $parent->render() . '/' . $model->filename();
	}
}
