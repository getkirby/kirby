<?php

namespace Kirby\Query\AST;

use Kirby\Query\Visitors\Visitor;

/**
 * Basic node representation in the query AST
 *
 * @package   Kirby Query
 * @author    Roman Steiner <roman@toastlab.ch>,
 *            Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @license   https://opensource.org/licenses/MIT
 * @since     5.1.0
 * @unstable
 *
 * @codeCoverageIgnore
 */
abstract class Node
{
	abstract public function resolve(Visitor $visitor);
}
