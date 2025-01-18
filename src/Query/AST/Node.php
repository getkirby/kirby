<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Basic query node representation
 *
 * @package   Kirby Query
 * @author    Roman Steiner <>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class Node
{
	abstract public function resolve(Visitor $visitor);
}
