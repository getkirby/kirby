<?php

namespace Kirby\Content;

use Kirby\Cache\MemoryCache;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;

/**
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class MemoryStorage extends Storage
{
	/**
	 * Cache instance, used to store content in memory
	 */
	protected MemoryCache $cache;

	/**
	 * Sets up the cache instance
	 */
	public function __construct(protected ModelWithContent $model)
	{
		parent::__construct($model);
		$this->cache = new MemoryCache();
	}

	/**
	 * Returns a unique id for a combination
	 * of the version id, the language code and the model id
	 */
	protected function cacheId(VersionId $versionId, Language $language): string
	{
		return $versionId->value() . '/' . $language->code() . '/' . $this->model->id() . '/' . spl_object_hash($this->model);
	}

	/**
	 * Deletes an existing version in an idempotent way if it was already deleted
	 */
	public function delete(VersionId $versionId, Language $language): void
	{
		$this->cache->remove($this->cacheId($versionId, $language));
	}

	/**
	 * Checks if a version exists
	 */
	public function exists(VersionId $versionId, Language $language): bool
	{
		return $this->cache->exists($this->cacheId($versionId, $language));
	}

	/**
	 * Returns the modification timestamp of a version if it exists
	 */
	public function modified(VersionId $versionId, Language $language): int|null
	{
		if ($this->exists($versionId, $language) === false) {
			return null;
		}

		return $this->cache->modified($this->cacheId($versionId, $language));
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 */
	public function read(VersionId $versionId, Language $language): array
	{
		return $this->cache->get($this->cacheId($versionId, $language)) ?? [];
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touch(VersionId $versionId, Language $language): void
	{
		$fields = $this->read($versionId, $language);
		$this->write($versionId, $language, $fields);
	}

	/**
	 * Writes the content fields of an existing version
	 *
	 * @param array<string, string> $fields Content fields
	 */
	protected function write(VersionId $versionId, Language $language, array $fields): void
	{
		$this->cache->set($this->cacheId($versionId, $language), $fields);
	}
}
