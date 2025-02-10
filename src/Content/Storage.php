<?php

namespace Kirby\Content;

use Generator;
use Kirby\Cms\Language;
use Kirby\Cms\Languages;
use Kirby\Cms\ModelWithContent;
use Kirby\Toolkit\A;

/**
 * Abstract for content storage handlers;
 * note that it is so far not viable to build custom
 * handlers because the CMS core relies on the filesystem
 * and cannot fully benefit from this abstraction yet
 * @internal
 * @since 4.0.0
 *
 * @package   Kirby Content
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
abstract class Storage
{
	public function __construct(protected ModelWithContent $model)
	{
	}

	/**
	 * Returns generator for all existing version-language combinations
	 *
	 * @return Generator<\Kirby\Content\VersionId, \Kirby\Cms\Language>
	 */
	public function all(): Generator
	{
		foreach (Languages::ensure() as $language) {
			foreach (VersionId::all() as $versionId) {
				if ($this->exists($versionId, $language) === true) {
					yield $versionId => $language;
				}
			}
		}
	}

	/**
	 * Copies content from one version-language combination to another
	 */
	public function copy(
		VersionId $fromVersionId,
		Language $fromLanguage,
		VersionId|null $toVersionId = null,
		Language|null $toLanguage = null,
		Storage|null $toStorage = null
	): void {
		// fallbacks to allow keeping the method call lean
		$toVersionId ??= $fromVersionId;
		$toLanguage  ??= $fromLanguage;
		$toStorage   ??= $this;

		// read the existing fields
		$content = $this->read($fromVersionId, $fromLanguage);

		// create the new version
		$toStorage->create($toVersionId, $toLanguage, $content);
	}

	/**
	 * Copies all content to another storage
	 */
	public function copyAll(Storage $to): void
	{
		foreach ($this->all() as $versionId => $language) {
			$this->copy($versionId, $language, toStorage: $to);
		}
	}

	/**
	 * Creates a new version
	 *
	 * @param array<string, string> $fields Content fields
	 */
	public function create(VersionId $versionId, Language $language, array $fields): void
	{
		$this->write($versionId, $language, $fields);
	}

	/**
	 * Deletes an existing version in an idempotent way if it was already deleted
	 */
	abstract public function delete(VersionId $versionId, Language $language): void;

	/**
	 * Deletes all versions when deleting a language
	 * @internal
	 * @todo Move to `Language` class
	 */
	public function deleteLanguage(Language $language): void
	{
		foreach (VersionId::all() as $versionId) {
			$this->delete($versionId, $language);
		}
	}

	/**
	 * Checks if a version exists
	 */
	abstract public function exists(VersionId $versionId, Language $language): bool;

	/**
	 * Creates a new storage instance with all the versions
	 * from the given storage instance.
	 */
	public static function from(self $fromStorage): static
	{
		$toStorage = new static(
			model: $fromStorage->model()
		);

		// copy all versions from the given storage instance
		// and add them to the new storage instance.
		$fromStorage->copyAll($toStorage);

		return $toStorage;
	}

	/**
	 * Returns the related model
	 */
	public function model(): ModelWithContent
	{
		return $this->model;
	}

	/**
	 * Returns the modification timestamp of a version if it exists
	 */
	abstract public function modified(VersionId $versionId, Language $language): int|null;

	/**
	 * Moves content from one version-language combination to another
	 */
	public function move(
		VersionId $fromVersionId,
		Language $fromLanguage,
		VersionId|null $toVersionId = null,
		Language|null $toLanguage = null,
		Storage|null $toStorage = null
	): void {
		// fallbacks to allow keeping the method call lean
		$toVersionId ??= $fromVersionId;
		$toLanguage  ??= $fromLanguage;
		$toStorage   ??= $this;

		// copy content to new version
		$this->copy(
			$fromVersionId,
			$fromLanguage,
			$toVersionId,
			$toLanguage,
			$toStorage
		);

		// clean up the old version
		$this->delete($fromVersionId, $fromLanguage);
	}

	/**
	 * Moves all content to another storage
	 */
	public function moveAll(Storage $to): void
	{
		foreach ($this->all() as $versionId => $language) {
			$this->move($versionId, $language, toStorage: $to);
		}
	}

	/**
	 * Adapts all versions when converting languages
	 * @internal
	 * @todo Move to `Language` class
	 */
	public function moveLanguage(
		Language $fromLanguage,
		Language $toLanguage
	): void {
		foreach (VersionId::all() as $versionId) {
			if ($this->exists($versionId, $fromLanguage) === true) {
				$this->move($versionId, $fromLanguage, toLanguage: $toLanguage);
			}
		}
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 */
	abstract public function read(VersionId $versionId, Language $language): array;

	/**
	 * Searches and replaces one or multiple strings
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function replaceStrings(
		VersionId $versionId,
		Language $language,
		array $map
	): void {
		$fields = $this->read($versionId, $language);
		$fields = A::map(
			$fields,
			fn ($field) => str_replace(
				array_keys($map),
				array_values($map),
				$field
			)
		);
		$this->update($versionId, $language, $fields);
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	abstract public function touch(VersionId $versionId, Language $language): void;

	/**
	 * Touches all versions of a language
	 * @internal
	 * @todo Move to `Language` class
	 */
	public function touchLanguage(Language $language): void
	{
		foreach (VersionId::all() as $versionId) {
			if ($this->exists($versionId, $language) === true) {
				$this->touch($versionId, $language);
			}
		}
	}

	/**
	 * Updates the content fields of an existing version
	 *
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\Exception If the file cannot be written
	 */
	public function update(VersionId $versionId, Language $language, array $fields): void
	{
		$this->write($versionId, $language, $fields);
	}

	/**
	 * Writes the content fields of an existing version
	 *
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\Exception If the content cannot be written
	 */
	abstract protected function write(VersionId $versionId, Language $language, array $fields): void;
}
