<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Horizontal rule
 *
 * You can produce a horizontal rule tag (<hr />) by placing
 * three or more hyphens, asterisks, or underscores on a line
 * by themselves. If you wish, you may use spaces between the
 * hyphens or asterisks.
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
class HorizontalRule extends Block
{
	public static function markers(): array
	{
		return ['*', '-', '_'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		$marker = $line->marker();

		// the rtrim scan fails fast on the first foreign character (e.g. the
		// `e` in `*emphasis*`), so it gates the full-string substr_count of has()
		if (
			rtrim($line->text(), ' ' . $marker) === '' &&
			$line->has($marker, 3)
		) {
			$line->next();

			return new Element('hr');
		}

		return false;
	}
}
