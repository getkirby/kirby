<?php

namespace Kirby\Parsley;

/**
 * Schema definition how to parse
 * the HTML document into blocks
 *
 * @package   Kirby Parsley
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.5.0
 */
class Schema
{
	/**
	 * Returns the fallback block when no
	 * other block type can be detected
	 */
	public function fallback(Element|string $element): array|null
	{
		return null;
	}

	/**
	 * Returns a list of allowed inline marks
	 * and their parsing rules
	 */
	public function marks(): array
	{
		return [];
	}

	/**
	 * Returns a list of allowed nodes and
	 * their parsing rules
	 */
	public function nodes(): array
	{
		return [];
	}

	/**
	 * Returns a list of all elements that should be
	 * skipped and not be parsed at all
	 */
	public function skip(): array
	{
		return [];
	}
}
