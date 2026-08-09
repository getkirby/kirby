<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\File;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;

/**
 * UUID for $file
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.8.0
 *
 * @extends ModelUuid<File>
 */
class FileUuid extends ModelUuid
{
	protected const string TYPE = 'file';

	/**
	 * Looks up UUID in cache and resolves to file object;
	 * special for `FileUuid` as the value stored in cache is
	 * a hybrid URI from the parent's UUID and filename; needs
	 * to resolve parent UUID and then get file by filename
	 */
	protected function findByCache(): File|null
	{
		// get mixed Uri from cache
		if ($key = $this->key()) {
			if ($value = Uuids::cache()->get($key)) {
				// value is an array containing
				// the UUID for the parent and the filename
				/** @var Page|Site|User|null $parent */
				$parent = Uuid::from($value['parent'])?->model();
				$file   = $parent?->file($value['filename']);

				// the cached parent/filename pair can be stale,
				// e.g. when the file has been renamed or copied
				if ($this->isFor($file) === true) {
					return $file;
				}
			}
		}

		return null;
	}

	/**
	 * Generator for all files in the site
	 * (of all pages, users and site)
	 *
	 * @return Generator<string, File>
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
	 * Returns the file object
	 */
	public function model(bool $lazy = false): File|null
	{
		return parent::model($lazy);
	}

	/**
	 * Returns value to be stored in cache
	 */
	public function value(): array
	{
		$model  = $this->model();
		$parent = Uuid::for($model->parent());

		// populate parent to cache itself as we'll need it
		// as well when resolving model later on
		$parent->populate();

		return [
			'parent'   => $parent->toString(),
			'filename' => $model->filename()
		];
	}

	/**
	 * Returns permalink url
	 */
	public function toPermalink(): string
	{
		return (new Permalink($this))->url();
	}
}
