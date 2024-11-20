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
	 * Returns whether the cache has been populated
	 */
	public function cacheExists(): bool
	{
		return $this->cache()->get('__updated__') !== null;
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
	public function files(): Files
	{
		$files = new Files([]);

		foreach ($this->read('files') as $id) {
			if ($file = $this->kirby->file($id)) {
				$files->add($file);
			}
		}

		return $this->ensure($files);
	}

	/**
	 * Rebuilds the cache by finding all models with changes version
	 */
	public function generateCache(): void
	{
		$models = [
			'files' => [],
			'pages' => [],
			'users' => []
		];

		foreach ($this->kirby->models() as $model) {
			if ($model->version(VersionId::changes())->exists('*') === true) {
				$models[$this->cacheKey($model)][] = (string)($model->uuid() ?? $model->id());
			}
		}

		foreach ($models as $key => $changes) {
			$this->update($key, $changes);
		}
	}

	/**
	 * Return all pages with unsaved changes
	 */
	public function pages(): Pages
	{
		/**
		 * @var \Kirby\Cms\Pages $pages
		 */
		$pages = $this->kirby->site()->find(
			false,
			false,
			...$this->read('pages')
		);

		return $this->ensure($pages);
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

		$changes   = $this->read($key);
		$changes[] = (string)($model->uuid() ?? $model->id());

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
			fn ($id) => $id !== (string)($model->uuid() ?? $model->id())
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
		$this->cache()->set('__updated__', time());
	}

	/**
	 * Return all users with unsaved changes
	 */
	public function users(): Users
	{
		/**
		 * @var \Kirby\Cms\Users $users
		 */
		$users = $this->kirby->users()->find(
			false,
			false,
			...$this->read('users')
		);

		return $this->ensure($users);
	}
}
