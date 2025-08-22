<?php

namespace Kirby\Uuid;

use Generator;
use Kirby\Cms\File;
use Override;

/**
 * UUID for \Kirby\Cms\File
 *
 * @package   Kirby Uuid
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.8.0
 *
 * @method \Kirby\Cms\File|null model(bool $lazy = false)
 */
class FileUuid extends ModelUuid
{
	protected const string TYPE = 'file';

	/**
	 * @var \Kirby\Cms\File|null
	 */
	public Identifiable|null $model = null;

	/**
	 * Looks up UUID in cache and resolves to file object;
	 * special for `FileUuid` as the value stored in cache is
	 * a hybrid URI from the parent's UUID and filename; needs
	 * to resolve parent UUID and then get file by filename
	 */
	#[Override]
	protected function findByCache(): File|null
	{
		// get mixed Uri from cache
		if ($key = $this->key()) {
			if ($value = Uuids::cache()->get($key)) {
				// value is an array containing
				// the UUID for the parent and the filename
				$parent = Uuid::for($value['parent'])->model();
				return $parent?->file($value['filename']);
			}
		}

		return null;
	}

	/**
	 * Generator for all files in the site
	 * (of all pages, users and site)
	 *
	 * @return \Generator|\Kirby\Cms\File[]
	 */
	#[Override]
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
	#[Override]
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
