<?php

namespace Kirby\Content;

/**
 * A separate cache instance for version memory
 * values to avoid conflicts with the main version cache.
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
class VersionMemoryCache extends VersionCache
{
	public static array $cache = [];
}
