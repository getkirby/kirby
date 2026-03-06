<?php

namespace Kirby\Content;

use Kirby\Cms\File;
use Kirby\Cms\Language;
use Kirby\Data\Frontmatter;
use Kirby\Exception\Exception;
use Kirby\Filesystem\F;

/**
 * Content storage handler using plain text files
 * with YAML frontmatter format
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     5.4.0
 */
class FrontmatterStorage extends PlainTextStorage
{
	/**
	 * Returns the stored content fields
	 *
	 * @return array<string, string>
	 */
	public function read(VersionId $versionId, Language $language): array
	{
		$contentFile = $this->contentFile($versionId, $language);

		if (file_exists($contentFile) === true) {
			return Frontmatter::decode(F::read($contentFile));
		}

		return [];
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

		// for file models with no fields, clean up rather than write an empty file
		if ($this->model instanceof File && $fields === []) {
			$this->delete($versionId, $language);
			return;
		}

		$success = F::write(
			$this->contentFile($versionId, $language),
			Frontmatter::encode($fields)
		);

		// @codeCoverageIgnoreStart
		if ($success !== true) {
			throw new Exception(message: 'Could not write the content file');
		}
		// @codeCoverageIgnoreEnd
	}
}
