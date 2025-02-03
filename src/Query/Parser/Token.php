<?php

namespace Kirby\Query\Parser;

/**
 * Represents a single token of a particular type
 * within a query
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Token
{
	public function __construct(
		public TokenType $type,
		public string $lexeme,
		public mixed $literal = null,
	) {
	}

	public function is(TokenType $type): bool
	{
		return $this->type === $type;
	}
}
