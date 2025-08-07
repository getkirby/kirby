<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Cms\User;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Exception\LogicException;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

/**
 * Content storage handler using plain text files
 * stored in the content folder
 *
 * @package   Kirby Content
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     4.0.0
 * @unstable
 */
class PlainTextStorage extends Storage
{
	/**
	 * Creates the absolute directory path for the model
	 */
	protected function contentDirectory(VersionId $versionId): string
	{
		$directory = match (true) {
			$this->model instanceof File
				=> dirname($this->model->root()),
			default
			=> $this->model->root()
		};

		if ($versionId->is('changes')) {
			$directory .= '/_changes';
		}

		return $directory;
	}

	/**
	 * Returns the absolute path to the content file
	 * @internal To be made `protected` when the CMS core no longer relies on it
	 */
	public function contentFile(VersionId $versionId, Language $language): string
	{
		// get the filename without extension and language code
		return match (true) {
			$this->model instanceof File => $this->contentFileForFile($this->model, $versionId, $language),
			$this->model instanceof Page => $this->contentFileForPage($this->model, $versionId, $language),
			$this->model instanceof Site => $this->contentFileForSite($this->model, $versionId, $language),
			$this->model instanceof User => $this->contentFileForUser($this->model, $versionId, $language),
			// @codeCoverageIgnoreStart
			default => throw new LogicException(
				message: 'Cannot determine content file for model type "' . $this->model::CLASS_ALIAS . '"'
			)
			// @codeCoverageIgnoreEnd
		};
	}

	/**
	 * Returns the absolute path to the content file of a file model
	 */
	protected function contentFileForFile(File $model, VersionId $versionId, Language $language): string
	{
		return $this->contentDirectory($versionId) . '/' . $this->contentFilename($model->filename(), $language);
	}

	/**
	 * Returns the absolute path to the content file of a page model
	 */
	protected function contentFileForPage(Page $model, VersionId $versionId, Language $language): string
	{
		return $this->contentDirectory($versionId) . '/' . $this->contentFilename($model->intendedTemplate()->name(), $language);
	}

	/**
	 * Returns the absolute path to the content file of a site model
	 */
	protected function contentFileForSite(Site $model, VersionId $versionId, Language $language): string
	{
		return $this->contentDirectory($versionId) . '/' . $this->contentFilename('site', $language);
	}

	/**
	 * Returns the absolute path to the content file of a user model
	 */
	protected function contentFileForUser(User $model, VersionId $versionId, Language $language): string
	{
		return $this->contentDirectory($versionId) . '/' . $this->contentFilename('user', $language);
	}

	/**
	 * Creates a filename with extension and optional language code
	 * in a multi-language installation
	 */
	protected function contentFilename(string $name, Language $language): string
	{
		$kirby     = $this->model->kirby();
		$extension = $kirby->contentExtension();

		if ($language->isSingle() === false) {
			return $name . '.' . $language->code() . '.' . $extension;
		}

		return $name . '.' . $extension;
	}

	/**
	 * Returns an array with content files of all languages
	 * @internal To be made `protected` when the CMS core no longer relies on it
	 */
	public function contentFiles(VersionId $versionId): array
	{
		if ($this->model->kirby()->multilang() === true) {
			return $this->model->kirby()->languages()->values(
				fn ($language) => $this->contentFile($versionId, $language)
			);
		}

		return [
			$this->contentFile($versionId, Language::single())
		];
	}

	/**
	 * Deletes an existing version in an idempotent way if it was already deleted
	 */
	public function delete(VersionId $versionId, Language $language): void
	{
		$contentFile = $this->contentFile($versionId, $language);

		// @codeCoverageIgnoreStart
		if (F::unlink($contentFile) !== true) {
			throw new Exception(message: 'Could not delete content file');
		}
		// @codeCoverageIgnoreEnd

		$contentDirectory = $this->contentDirectory($versionId);

		// clean up empty content directories (_changes or the page/user directory)
		$this->deleteEmptyDirectory($contentDirectory);

		// delete empty _drafts directories for pages
		if (
			$versionId->is('latest') === true &&
			$this->model instanceof Page &&
			$this->model->isDraft() === true
		) {
			$this->deleteEmptyDirectory(dirname($contentDirectory));
		}
	}

	/**
	 * Helper to delete empty _changes directories
	 *
	 * @throws \Kirby\Exception\Exception if the directory cannot be deleted
	 */
	protected function deleteEmptyDirectory(string $directory): void
	{
		if (
			Dir::exists($directory) === true &&
			Dir::isEmpty($directory) === true
		) {
			// @codeCoverageIgnoreStart
			if (Dir::remove($directory) !== true) {
				throw new Exception(
					message: 'Could not delete empty content directory'
				);
			}
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Checks if a version exists
	 */
	public function exists(VersionId $versionId, Language $language): bool
	{
		$contentFile = $this->contentFile($versionId, $language);

		// The version definitely exists, if there's a
		// matching content file
		if (file_exists($contentFile) === true) {
			return true;
		}

		// A changed version or non-default language version does not exist
		// if the content file was not found
		if (
			$versionId->is('latest') === false ||
			$language->isDefault() === false
		) {
			return false;
		}

		// Whether the default version exists,
		// depends on different cases for each model.
		// Page, Site and User exist as soon as the folder is there.
		// A File exists as soon as the file is there.
		return match (true) {
			$this->model instanceof File => is_file($this->model->root()) === true,
			$this->model instanceof Page,
			$this->model instanceof Site,
			$this->model instanceof User => is_dir($this->model->root()) === true,
			// @codeCoverageIgnoreStart
			default => throw new LogicException(
				message: 'Cannot determine existence for model type "' . $this->model::CLASS_ALIAS . '"'
			)
			// @codeCoverageIgnoreEnd
		};
	}

	/**
	 * Compare two version-language-storage combinations
	 */
	public function isSameStorageLocation(
		VersionId $fromVersionId,
		Language $fromLanguage,
		VersionId|null $toVersionId = null,
		Language|null $toLanguage = null,
		Storage|null $toStorage = null
	) {
		// fallbacks to allow keeping the method call lean
		$toVersionId ??= $fromVersionId;
		$toLanguage  ??= $fromLanguage;
		$toStorage   ??= $this;

		// no need to compare content files if the new
		// storage type is different
		if ($toStorage instanceof self === false) {
			return false;
		}

		$contentFileA = $this->contentFile($fromVersionId, $fromLanguage);
		$contentFileB = $toStorage->contentFile($toVersionId, $toLanguage);

		return $contentFileA === $contentFileB;
	}

	/**
	 * Returns the modification timestamp of a version
	 * if it exists
	 */
	public function modified(VersionId $versionId, Language $language): int|null
	{
		$modified = F::modified($this->contentFile($versionId, $language));

		if (is_int($modified) === true) {
			return $modified;
		}

		return null;
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 */
	public function read(VersionId $versionId, Language $language): array
	{
		$contentFile = $this->contentFile($versionId, $language);

		if (file_exists($contentFile) === true) {
			return Data::read($contentFile);
		}

		// For existing versions that don't have a content file yet,
		// we can safely return an empty array that can be filled later.
		// This might be the case for pages that only have a directory
		// so far, or for files that don't have any metadata yet.
		return [];
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @throws \Kirby\Exception\Exception If the file cannot be touched
	 */
	public function touch(VersionId $versionId, Language $language): void
	{
		$success = touch($this->contentFile($versionId, $language));

		// @codeCoverageIgnoreStart
		if ($success !== true) {
			throw new Exception(
				message: 'Could not touch existing content file'
			);
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Writes the content fields of an existing version
	 *
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\Exception If the content cannot be written
	 */
	protected function write(VersionId $versionId, Language $language, array $fields): void
	{
		// only store non-null value fields
		$fields = array_filter($fields, fn ($field) => $field !== null);

		// Content for files is only stored when there are any fields.
		// Otherwise, the storage handler will take care here of cleaning up
		// unnecessary content files.
		if ($this->model instanceof File && $fields === []) {
			$this->delete($versionId, $language);
			return;
		}

		$success = Data::write($this->contentFile($versionId, $language), $fields);

		// @codeCoverageIgnoreStart
		if ($success !== true) {
			throw new Exception(message: 'Could not write the content file');
		}
		// @codeCoverageIgnoreEnd
	}

}
