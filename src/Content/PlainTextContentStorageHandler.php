<?php

namespace Kirby\Content;

use Kirby\Cms\File;
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
	public function contentDirectory(VersionId $versionId): string
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
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	public function contentFile(VersionId $versionId, string $lang): string
	{
		// get the filename without extension and language code
		return match (true) {
			$this->model instanceof File => $this->contentFileForFile($this->model, $versionId, $lang),
			$this->model instanceof Page => $this->contentFileForPage($this->model, $versionId, $lang),
			$this->model instanceof Site => $this->contentFileForSite($this->model, $versionId, $lang),
			$this->model instanceof User => $this->contentFileForUser($this->model, $versionId, $lang),
			// @codeCoverageIgnoreStart
			default => throw new LogicException('Cannot determine content file for model type "' . $this->model::CLASS_ALIAS . '"')
			// @codeCoverageIgnoreEnd
		};
	}

	/**
	 * Returns the absolute path to the content file of a file model
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	protected function contentFileForFile(File $model, VersionId $versionId, string $lang): string
	{
		return $this->contentDirectory($versionId) . '/' . $this->contentFilename($model->filename(), $lang);
	}

	/**
	 * Returns the absolute path to the content file of a page model
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	protected function contentFileForPage(Page $model, VersionId $versionId, string $lang): string
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

		return $directory . '/' . $this->contentFilename($model->intendedTemplate()->name(), $lang);
	}

	/**
	 * Returns the absolute path to the content file of a site model
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	protected function contentFileForSite(Site $model, VersionId $versionId, string $lang): string
	{
		return $this->contentDirectory($versionId) . '/' . $this->contentFilename('site', $lang);
	}

	/**
	 * Returns the absolute path to the content file of a user model
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	protected function contentFileForUser(User $model, VersionId $versionId, string $lang): string
	{
		return $this->contentDirectory($versionId) . '/' . $this->contentFilename('user', $lang);
	}

	/**
	 * Creates a filename with extension and optional language code
	 * in a multi-language installation
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	public function contentFilename(string $name, string $lang): string
	{
		$kirby     = $this->model->kirby();
		$extension = $kirby->contentExtension();

		if ($lang !== 'default') {
			return $name . '.' . $lang . '.' . $extension;
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
				fn ($lang) => $this->contentFile($versionId, $lang)
			);
		}

		return [
			$this->contentFile($versionId, 'default')
		];
	}

	/**
	 * Creates a new version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\Exception If the file cannot be written
	 */
	public function create(VersionId $versionId, string $lang, array $fields): void
	{
		$this->write($versionId, $lang, $fields);
	}

	/**
	 * Deletes an existing version in an idempotent way if it was already deleted
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	public function delete(VersionId $versionId, string $lang): void
	{
		$contentFile = $this->contentFile($versionId, $lang);
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
	 *
	 * @param string|null $lang Code `'default'` in a single-lang installation;
	 *                          checks for "any language" if not provided
	 */
	public function exists(VersionId $versionId, string|null $lang): bool
	{
		if ($lang === null) {
			foreach ($this->contentFiles($versionId) as $file) {
				if (is_file($file) === true) {
					return true;
				}
			}

			return false;
		}

		return is_file($this->contentFile($versionId, $lang)) === true;
	}

	/**
	 * Returns the modification timestamp of a version
	 * if it exists
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	public function modified(VersionId $versionId, string $lang): int|null
	{
		$modified = F::modified($this->contentFile($versionId, $lang));

		if (is_int($modified) === true) {
			return $modified;
		}

		return null;
	}

	/**
	 * Moves content from one version-language combination to another
	 *
	 * @param string $fromLang Code `'default'` in a single-lang installation
	 * @param string $toLang Code `'default'` in a single-lang installation
	 */
	public function move(
		VersionId $fromVersionId,
		string $fromLang,
		VersionId $toVersionId,
		string $toLang
	): void {
		F::move(
			$this->contentFile($fromVersionId, $fromLang),
			$this->contentFile($toVersionId, $toLang)
		);
	}

	/**
	 * Returns the stored content fields
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @return array<string, string>
	 */
	public function read(VersionId $versionId, string $lang): array
	{
		return Data::read($this->contentFile($versionId, $lang));
	}

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 *
	 * @throws \Kirby\Exception\Exception If the file cannot be touched
	 */
	public function touch(VersionId $versionId, string $lang): void
	{
		$success = touch($this->contentFile($versionId, $lang));

		// @codeCoverageIgnoreStart
		if ($success !== true) {
			throw new Exception('Could not touch existing content file');
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Updates the content fields of an existing version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\Exception If the file cannot be written
	 */
	public function update(VersionId $versionId, string $lang, array $fields): void
	{
		$this->write($versionId, $lang, $fields);
	}

	/**
	 * Writes the content fields of an existing version
	 *
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\Exception If the content cannot be written
	 */
	protected function write(VersionId $versionId, string $lang, array $fields): void
	{
		$success = Data::write($this->contentFile($versionId, $lang), $fields);

		// @codeCoverageIgnoreStart
		if ($success !== true) {
			throw new Exception('Could not write the content file');
		}
		// @codeCoverageIgnoreEnd
	}

}
