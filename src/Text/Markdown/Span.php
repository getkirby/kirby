<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Base for a single inline-level Markdown component
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
abstract class Span extends Component
{
	/**
	 * Tries to consume a $phrase from the current trigger.
	 * Records the consumed extent on the $phrase and returns
	 * the generated Node, `null` if it matched but emits nothing,
	 * or `false` if the marker does not belong to this span.
	 */
	abstract public function consume(Phrase $phrase): Node|false|null;
}
