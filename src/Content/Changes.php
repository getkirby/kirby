<?php

namespace Kirby\Content;

use Kirby\Cache\Cache;
use Kirby\Cms\App;
use Kirby\Cms\Files;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Pages;
use Kirby\Cms\Users;
use Kirby\Toolkit\A;

/**
 * The Changes class tracks changed models
 * in the Site's changes field.
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Changes
{
	protected App $kirby;

	public function __construct()
	{
		$this->kirby = App::instance();
	}

	/**
	 * Access helper for the cache, in which changes are stored
	 */
	public function cache(): Cache
	{
		return $this->kirby->cache('changes');
	}

	/**
	 * Returns the cache key for a given model
	 */
	public function cacheKey(ModelWithContent $model): string
	{
		return $model::CLASS_ALIAS . 's';
	}

	/**
	 * Verify that the tracked model still really has changes.
	 * If not, untrack and remove from collection.
	 *
	 * @template T of \Kirby\Cms\Files|\Kirby\Cms\Pages|\Kirby\Cms\Users
	 * @param T $tracked
	 * @return T
	 */
	public function ensure(Files|Pages|Users $tracked): Files|Pages|Users
	{
		foreach ($tracked as $model) {
			if ($model->version(VersionId::changes())->exists('*') === false) {
				$this->untrack($model);
				$tracked->remove($model);
			}
		}

		return $tracked;
	}

	/**
	 * Return all files with unsaved changes
	 */
	public function files(bool $ensure = true): Files
	{
		$files = new Files([]);

		foreach ($this->read('files') as $id) {
			if ($file = $this->kirby->file($id)) {
				$files->add($file);
			}
		}

		if ($ensure === true) {
			$files = $this->ensure($files);
		}

		return $files;
	}

	/**
	 * Return all pages with unsaved changes
	 */
	public function pages(bool $ensure = true): Pages
	{
		/**
		 * @var \Kirby\Cms\Pages $pages
		 */
		$pages = $this->kirby->site()->find(
			false,
			false,
			...$this->read('pages')
		);

		if ($ensure === true) {
			$pages = $this->ensure($pages);
		}

		return $pages;
	}

	/**
	 * Read the changes for a given model type
	 */
	public function read(string $key): array
	{
		return $this->cache()->get($key) ?? [];
	}

	/**
	 * Add a new model to the list of unsaved changes
	 */
	public function track(ModelWithContent $model): void
	{
		$key = $this->cacheKey($model);

		$changes = $this->read($key);
		$changes[] = (string)$model->uuid();

		$this->update($key, $changes);
	}

	/**
	 * Remove a model from the list of unsaved changes
	 */
	public function untrack(ModelWithContent $model): void
	{
		// get the cache key for the model type
		$key = $this->cacheKey($model);

		// remove the model from the list of changes
		$changes = A::filter(
			$this->read($key),
			fn ($uuid) => $uuid !== (string)$model->uuid()
		);

		$this->update($key, $changes);
	}

	/**
	 * Update the changes field
	 */
	public function update(string $key, array $changes): void
	{
		$changes = array_unique($changes);
		$changes = array_values($changes);

		$this->cache()->set($key, $changes);
	}

	/**
	 * Return all users with unsaved changes
	 */
	public function users(bool $ensure = true): Users
	{
		/**
		 * @var \Kirby\Cms\Users $users
		 */
		$users = $this->kirby->users()->find(
			false,
			false,
			...$this->read('users')
		);

		if ($ensure === true) {
			$users = $this->ensure($users);
		}

		return $users;
	}
}
