<?php

namespace Kirby\Content;

use Kirby\Cms\Language;

/**
 * The Version cache class keeps content fields
 * to avoid multiple storage reads for the same
 * content.
 *
 * @internal
 * @since 5.0.0
 *
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class VersionCache
{
	/**
	 * All cache values for all versions
	 * and language combinations
	 */
	public static array $cache = [];

	/**
	 * Creates a unique cache key for any version/language combination
	 */
	public static function key(Version $version, Language $language): string
	{
		return spl_object_hash($version->model()) . ':' . $version->id() . ':' . $language->code();
	}

	/**
	 * Tries to receive a fields for a version/language combination
	 */
	public static function get(Version $version, Language $language): array|null
	{
		return static::$cache[static::key($version, $language)] ?? null;
	}

	/**
	 * Removes fields for a version/language combination
	 */
	public static function remove(Version $version, Language $language): void
	{
		unset(static::$cache[static::key($version, $language)]);
	}

	/**
	 * Keeps fields for a version/language combination
	 */
	public static function set(Version $version, Language $language, array $fields = []): void
	{
		static::$cache[static::key($version, $language)] = $fields;
	}
}
