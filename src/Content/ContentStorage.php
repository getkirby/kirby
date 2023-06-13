<?php

namespace Kirby\Content;

use Kirby\Cms\Language;
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
interface ContentStorage
{
	public function __construct(ModelWithContent $model);

	/**
	 * Creates a new version
	 *
	 * @param \Kirby\Cms\Language $lang Language with code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 */
	public function create(VersionTemplate $type, Language $lang, array $fields): VersionIdentifier;

	/**
	 * Deletes an existing version in an idempotent way if it was already deleted
	 *
	 * @param \Kirby\Cms\Language $lang Language with code `'default'` in a single-lang installation
	 */
	public function delete(VersionIdentifier $version, Language $lang): void;

	/**
	 * Checks if a version exists
	 *
	 * @param \Kirby\Cms\Language|null $lang Language with code `'default'` in a single-lang installation;
	 *                                       checks for "any language" if not provided
	 */
	public function exists(VersionIdentifier $version, Language|null $lang): bool;

	/**
	 * Returns the modification timestamp of a version
	 * if it exists
	 *
	 * @param \Kirby\Cms\Language $lang Language with code `'default'` in a single-lang installation
	 */
	public function modified(VersionIdentifier $version, Language $lang): int|null;

	/**
	 * Returns the stored content fields
	 *
	 * @param \Kirby\Cms\Language $lang Language with code `'default'` in a single-lang installation
	 * @return array<string, string>
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function read(VersionIdentifier $version, Language $lang): array;

	/**
	 * Updates the modification timestamp of an existing version
	 *
	 * @param \Kirby\Cms\Language $lang Language with code `'default'` in a single-lang installation
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function touch(VersionIdentifier $version, Language $lang): void;

	/**
	 * Updates the content fields of an existing version
	 *
	 * @param \Kirby\Cms\Language $lang Language with code `'default'` in a single-lang installation
	 * @param array<string, string> $fields Content fields
	 *
	 * @throws \Kirby\Exception\NotFoundException If the version does not exist
	 */
	public function update(VersionIdentifier $version, Language $lang, array $fields): void;
}
