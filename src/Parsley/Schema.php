<?php

namespace Kirby\Parsley;

/**
 * Schema definition how to parse
 * the HTML document into blocks
 *
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
	 *
	 * @return list<array>
	 */
	public function marks(): array
	{
		return [];
	}

	/**
	 * Returns a list of allowed nodes and
	 * their parsing rules
	 *
	 * @return list<array>
	 */
	public function nodes(): array
	{
		return [];
	}

	/**
	 * Returns a list of all elements that should be
	 * skipped and not be parsed at all
	 *
	 * @return list<string>
	 */
	public function skip(): array
	{
		return [];
	}
}
