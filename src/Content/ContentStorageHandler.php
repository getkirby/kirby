<?php

namespace Kirby\Content;

use Kirby\Cms\ModelWithContent;

/**
 * Interface for content storage handlers;
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
interface ContentStorageHandler
{
	public function __construct(ModelWithContent $model);

	/**
	 * Creates a new version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 */
	public function create(string $versionType, string $lang, array $fields): void;

	/**
	 * Deletes an existing version in an idempotent way if it was already deleted
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	public function delete(string $version, string $lang): void;

	/**
	 * Checks if a version exists
	 *
	 * @param string|null $lang Code `'default'` in a single-lang installation;
	 *                          checks for "any language" if not provided
	 */
	public function exists(string $version, string|null $lang): bool;

	/**
	 * Returns the modification timestamp of a version if it exists
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 */
	public function modified(string $version, string $lang): int|null;

	/**
	 * Moves content from one version-language combination to another
	 *
	 * @param string $fromLang Code `'default'` in a single-lang installation
	 * @param string $toLang Code `'default'` in a single-lang installation
	 */
	public function move(
		string $fromVersion,
		string $fromLang,
		string $toVersion,
		string $toLang
	): void;

	/**
	 * Returns the stored content fields
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @return array<string, string>
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function read(string $version, string $lang): array;

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touch(string $version, string $lang): void;

	/**
	 * Updates the content fields of an existing version
	 *
	 * @param string $lang Code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function update(string $version, string $lang, array $fields): void;
}
