<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Delimiter;

/**
 * Emphasis and strong emphasis via `*`
 *
 * @example
 * This wil be an *em* tag.
 * This wil be a **strong** tag.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Emphasis extends DelimitedInline
{
	public static function markers(): array
	{
		return ['*'];
	}

	/**
	 * An intraword `*` pair may not span whitespace,
	 * e.g. `Lehrer*innen und Schüler*innen` should stay literal.
	 */
	public function rejectsWhitespace(Delimiter $open, Delimiter $close): bool
	{
		return
			$open->intrawordBefore === true &&
			$close->intrawordAfter === true;
	}

	/**
	 * Classic emphasis obeys CommonMark's rule of three.
	 */
	public function ruleOfThree(Delimiter $open, Delimiter $close): bool
	{
		return
			($open->canClose === true || $close->canOpen === true) &&
			$close->original % 3 !== 0 &&
			($open->original + $close->original) % 3 === 0;
	}

	/**
	 * Two or more markers pair into `<strong>`, a single one into `<em>`.
	 */
	protected function tags(): array
	{
		return [2 => 'strong', 1 => 'em'];
	}
}
