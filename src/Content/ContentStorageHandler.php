<?php

namespace Kirby\Content;

use Generator;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Cms\Page;

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
	 *
	 * @return Generator<VersionId|Language>
	 * @todo 5.0.0 Consider more descriptive name and maybe move to a different class
	 */
	public function all(): Generator
	{
		$kirby     = $this->model->kirby();
		$languages = $kirby->multilang() === false ? [Language::single()] : $kirby->languages();

		foreach ($languages as $language) {
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
	abstract public function create(VersionId $versionId, Language $language, array $fields): void;

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
	 *
	 * @todo We might want to move this directly to the models later or work
	 *       with a `Versions` class
	 *
	 * @internal
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
	 * Checks if a version exists
	 */
	abstract public function exists(VersionId $versionId, Language $language): bool;

	/**
	 * Returns the modification timestamp of a version if it exists
	 */
	abstract public function modified(VersionId $versionId, Language $language): int|null;

	/**
	 * Moves content from one version-language combination to another
	 */
	abstract public function move(
		VersionId $fromVersionId,
		Language $fromLanguage,
		VersionId $toVersionId,
		Language $toLanguage
	): void;

	/**
	 * Adapts all versions when converting languages
	 * @internal
	 * @todo Move to `Language` class
	 */
	public function moveLanguage(Language $fromLanguage, Language $toLanguage): void
	{
		foreach ($this->dynamicVersions() as $versionId) {
			$this->move($versionId, $fromLanguage, $versionId, $toLanguage);
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
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	abstract public function update(VersionId $versionId, Language $language, array $fields): void;
}
