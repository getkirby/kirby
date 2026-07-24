<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Block quote
 *
 * Markdown uses email-style > characters for blockquoting.
 *
 * @example
 * > This is a blockquote with two paragraphs. Lorem ipsum dolor sit amet,
 * > consectetuer adipiscing elit. Aliquam hendrerit mi posuere lectus.
 * >
 * > Donec sit amet nisl. Aliquam semper ipsum sit amet velit.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class BlockQuote extends ContainerBlock
{
	public static function markers(): array
	{
		return ['>'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		$text  = $line->text();
		$texts = [$this->strip($text)];
		$line->next();

		// as long as we have more lines…
		while ($line->valid() === true) {
			if ($line->isBlank() === true) {
				break;
			}

			$text = $line->text();

			// a `>`-prefixed line always belongs to the quote
			if (($text[0] ?? '') === '>') {
				$texts[] = $this->strip($text);
				$line->next();
				continue;
			}

			// a line without `>` continues the quote
			// only as paragraph continuation text
			if ($this->isLazyContinuation($texts, $line->body()) === false) {
				break;
			}

			$texts[] = $line->body();
			$line->next();
		}

		return new Element(
			name:      'blockquote',
			multiline: true,
			// eager-parse the content so a link
			// reference definition inside the quote registers
			// before any earlier reference is resolved
			children:  $this->parser->blocks()->parse($texts),
			block:     true
		);
	}

	/**
	 * Strips a leading `>` marker and one optional following space.
	 */
	protected function strip(string $text): string
	{
		return substr($text, ($text[1] ?? '') === ' ' ? 2 : 1);
	}
}
