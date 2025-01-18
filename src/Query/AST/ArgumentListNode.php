<?php

namespace Kirby\Query\AST;

/**
 * @package   Kirby Query
 * @author    Roman Steiner <>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class ArgumentListNode extends Node
{
	public function __construct(
		public array $arguments,
	) {
	}
}
