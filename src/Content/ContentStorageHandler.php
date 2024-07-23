<?php

namespace Kirby\Content;

use Generator;
use Kirby\Cms\Language;
use Kirby\Cms\Languages;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;
use Kirby\Exception\NotFoundException;

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
abstract class ContentStorageHandler
{
	public function __construct(protected ModelWithContent $model)
	{
	}

	/**
	 * Returns generator for all existing version-language combinations
	 * @todo 5.0.0 Consider more descriptive name and maybe move to a different class
	 *
	 * @return Generator<\Kirby\Content\VersionId, \Kirby\Cms\Language>
	 */
	public function all(): Generator
	{
		foreach (Languages::ensure() as $language) {
			foreach ($this->dynamicVersions() as $versionId) {
				if ($this->exists($versionId, $language) === true) {
					yield $versionId => $language;
				}
			}
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
		foreach ($this->dynamicVersions() as $version) {
			$this->delete($version, $language);
		}
	}

	/**
	 * Returns all versions available for the model that can be updated
	 * @internal
	 * @todo We might want to move this directly to the models later or work
	 *       with a `Versions` class
	 */
	public function dynamicVersions(): array
	{
		$versions = [VersionId::changes()];

		if (
			$this->model instanceof Page === false ||
			$this->model->isDraft() === false
		) {
			$versions[] = VersionId::published();
		}

		return $versions;
	}

	/**
	 * Checks if a version/language combination exists and otherwise
	 * will throw a `NotFoundException`
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function ensure(VersionId $versionId, Language $language): void
	{
		if ($this->exists($versionId, $language) === true) {
			return;
		}

		$message = match($this->model->kirby()->multilang()) {
			true  => 'Version "' . $versionId . ' (' . $language->code() . ')" does not already exist',
			false => 'Version "' . $versionId . '" does not already exist',
		};

		throw new NotFoundException($message);
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
		foreach ($fromStorage->all() as $versionId => $language) {
			$toStorage->create($versionId, $language, $fromStorage->read($versionId, $language));
		}

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
		VersionId $toVersionId,
		Language $toLanguage
	): void {
		// read the existing fields
		$fields = $this->read($fromVersionId, $fromLanguage);

		// create the new version
		$this->create($toVersionId, $toLanguage, $fields);

		// clean up the old version
		$this->delete($fromVersionId, $fromLanguage);
	}

	/**
	 * Adapts all versions when converting languages
	 * @internal
	 * @todo Move to `Language` class
	 */
	public function moveLanguage(Language $fromLanguage, Language $toLanguage): void
	{
		foreach ($this->dynamicVersions() as $versionId) {
			if ($this->exists($versionId, $fromLanguage) === true) {
				$this->move($versionId, $fromLanguage, $versionId, $toLanguage);
			}
		}
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	abstract public function read(VersionId $versionId, Language $language): array;

	public function replace(string $version, string $lang, array $search, array $replace): void;

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
		foreach ($this->dynamicVersions() as $version) {
			if ($this->exists($version, $language) === true) {
				$this->touch($version, $language);
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
		$this->ensure($versionId, $language);
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
