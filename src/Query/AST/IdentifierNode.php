<?php

namespace Kirby\Query\AST;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class IdentifierNode extends Node
{
	/**
	 * Replaces the escaped identifier with the actual identifier
	 */
	public static function unescape(string $name): string
	{
		return stripslashes($name);
	}
}
