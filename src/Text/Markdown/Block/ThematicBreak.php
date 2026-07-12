<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Horizontal rule
 *
 * @example
 * ***
 * - - -
 * ---------------------------------------
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class ThematicBreak extends LeafBlock
{
	public static function markers(): array
	{
		return ['*', '-', '_'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		if ($this->detects($line) === false) {
			return false;
		}

		$line->next();

		return new Element('hr');
	}

	/**
	 * Whether the line is a thematic break
	 */
	public function detects(Line $line): bool
	{
		$marker = $line->marker();

		// the rtrim scan fails fast on the first foreign character,
		// so it gates the full-string substr_count of $line->has()
		return
			($marker === '*' || $marker === '-' || $marker === '_') &&
			rtrim($line->text(), ' ' . $marker) === '' &&
			$line->has($marker, 3);
	}
}
