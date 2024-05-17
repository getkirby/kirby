<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
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
 * @internal
 * @since 4.0.0
 *
 * @package   Kirby Content
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class PlainTextContentStorageHandler implements ContentStorageHandler
{
	public function __construct(protected ModelWithContent $model)
	{
	}

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

		if ($versionId->is(VersionId::CHANGES)) {
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
			default => throw new LogicException('Cannot determine content file for model type "' . $this->model::CLASS_ALIAS . '"')
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
		$directory = $this->contentDirectory($versionId);

		if ($model->isDraft() === true) {
			if ($versionId->is(Versionid::PUBLISHED) === true) {
				throw new LogicException('Drafts cannot have a published content file');
			}

			// drafts already have the `_drafts` prefix in their root.
			// `_changes` must not be added to it in addition to that.
			$directory = $this->model->root();
		}

		return $directory . '/' . $this->contentFilename($model->intendedTemplate()->name(), $language);
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
	public function contentFilename(string $name, Language $language): string
	{
		$kirby     = $this->model->kirby();
		$extension = $kirby->contentExtension();

		if ($kirby->multilang() === true) {
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
	 * Creates a new version
	 *
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\Exception If the file cannot be written
	 */
	public function create(VersionId $versionId, Language $language, array $fields): void
	{
		$this->write($versionId, $language, $fields);
	}

	/**
	 * Deletes an existing version in an idempotent way if it was already deleted
	 */
	public function delete(VersionId $versionId, Language $language): void
	{
		$contentFile = $this->contentFile($versionId, $language);
		$success = F::unlink($contentFile);

		// @codeCoverageIgnoreStart
		if ($success !== true) {
			throw new Exception('Could not delete content file');
		}
		// @codeCoverageIgnoreEnd

		// clean up empty directories
		$contentDir = dirname($contentFile);
		if (
			Dir::exists($contentDir) === true &&
			Dir::isEmpty($contentDir) === true
		) {
			$success = rmdir($contentDir);

			// @codeCoverageIgnoreStart
			if ($success !== true) {
				throw new Exception('Could not delete empty content directory');
			}
			// @codeCoverageIgnoreEnd
		}
	}

	/**
	 * Checks if a version exists
	 */
	public function exists(VersionId $versionId, Language $language): bool
	{
		return is_file($this->contentFile($versionId, $language)) === true;
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
	 * Moves content from one version-language combination to another
	 */
	public function move(
		VersionId $fromVersionId,
		Language $fromLanguage,
		VersionId $toVersionId,
		Language $toLanguage
	): void {
		F::move(
			$this->contentFile($fromVersionId, $fromLanguage),
			$this->contentFile($toVersionId, $toLanguage)
		);
	}

	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 */
	public function read(VersionId $versionId, Language $language): array
	{
		return Data::read($this->contentFile($versionId, $language));
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
			throw new Exception('Could not touch existing content file');
		}
		// @codeCoverageIgnoreEnd
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
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\Exception If the content cannot be written
	 */
	protected function write(VersionId $versionId, Language $language, array $fields): void
	{
		$success = Data::write($this->contentFile($versionId, $language), $fields);

		// @codeCoverageIgnoreStart
		if ($success !== true) {
			throw new Exception('Could not write the content file');
		}
		// @codeCoverageIgnoreEnd
	}

}
