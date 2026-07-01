<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Block;
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
class Quote extends Block
{
	public static function markers(): array
	{
		return ['>'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		// dispatched on the `>` marker, so the line always starts with `>`:
		// strip it and one optional following space (the PATTERN capture)
		$text  = $line->text();
		$texts = [substr($text, ($text[1] ?? '') === ' ' ? 2 : 1)];
		$line->next();

		while ($line->valid() === true) {
			$text = $this->line($line);

			// a blank line (false) interrupts and closes the quote
			if ($text === false) {
				break;
			}

			$texts[] = $text;
			$line->next();
		}

		return new Element(
			name:      'blockquote',
			multiline: true,
			content:   $texts,
			block:     true
		);
	}

	protected function line(Line $line): string|false
	{
		// a blank line interrupts and closes the quote
		if ($line->isBlank() === true) {
			return false;
		}

		$text = $line->text();

		// `>`-prefixed line: strip the marker and one optional space
		if (($text[0] ?? '') === '>') {
			return substr($text, ($text[1] ?? '') === ' ' ? 2 : 1);
		}

		// lazy continuation line without `>`
		return $text;
	}
}
