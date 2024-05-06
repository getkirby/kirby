<?php

namespace Kirby\Content;

use Kirby\Cms\ModelWithContent;
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
	 * Creates a new version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 */
	public function create(VersionId $versionId, string $lang, array $fields): void
	{
		$success = Data::write($this->contentFile($versionId, $lang), $fields);

		// @codeCoverageIgnoreStart
		if ($success !== true) {
			throw new Exception('Could not write new content file');
		}
		// @codeCoverageIgnoreEnd
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
	 */
	public function update(VersionId $versionId, string $lang, array $fields): void
	{
		$success = Data::write($this->contentFile($versionId, $lang), $fields);

		// @codeCoverageIgnoreStart
		if ($success !== true) {
			throw new Exception('Could not write existing content file');
		}
		// @codeCoverageIgnoreEnd
	}

	/**
	 * Returns the absolute path to the content file
	 * @internal To be made `protected` when the CMS core no longer relies on it
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 *
	 * @throws \Kirby\Exception\LogicException If the model type doesn't have a known content filename
	 */
	public function contentFile(VersionId $versionId, string $lang): string
	{
		$extension = $this->model->kirby()->contentExtension();
		$directory = $this->model->root();

		$directory = match ($this->model::CLASS_ALIAS) {
			'file'  => dirname($this->model->root()),
			default => $this->model->root()
		};

		$filename = match ($this->model::CLASS_ALIAS) {
			'file'  => $this->model->filename(),
			'page'  => $this->model->intendedTemplate()->name(),
			'site',
			'user'  => $this->model::CLASS_ALIAS,
			// @codeCoverageIgnoreStart
			default => throw new LogicException('Cannot determine content filename for model type "' . $this->model::CLASS_ALIAS . '"')
			// @codeCoverageIgnoreEnd
		};

		if ($this->model::CLASS_ALIAS === 'page' && $this->model->isDraft() === true) {
			// changes versions don't need anything extra
			// (drafts already have the `_drafts` prefix in their root),
			// but a published version is not possible
			if ($versionId->is(VersionId::PUBLISHED)) {
				throw new LogicException('Drafts cannot have a published content file');
			}
		} elseif ($versionId->is(VersionId::CHANGES)) {
			// other model type or published page that has a changes subfolder
			$directory .= '/_changes';
		}

		if ($lang !== 'default') {
			return $directory . '/' . $filename . '.' . $lang . '.' . $extension;
		}

		return $directory . '/' . $filename . '.' . $extension;
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
}
