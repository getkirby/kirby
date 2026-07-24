<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Indented code block
 *
 * @example
 *     $foo = 'bar';
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class IndentedCode extends LeafBlock
{
	public static function markers(): array
	{
		return [self::INDENT];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		// an indented line should not interrupt a running paragraph
		if (
			$paragraph !== null &&
			$line->isBlank(offset: -1) === false
		) {
			return false;
		}

		$code   = $line->content(columns: 4);
		$blanks = [];
		$line->next();

		while ($line->valid() === true) {
			if ($line->isBlank() === true) {
				$blanks[] = $line->content(4);
				$line->next();
				continue;
			}

			if ($line->indent() < 4) {
				break;
			}

			foreach ($blanks as $blank) {
				$code .= "\n" . $blank;
			}

			$blanks = [];
			$code  .= "\n" . $line->content(columns: 4);

			$line->next();
		}

		// the content keeps the terminating newline of its last line
		$code .= "\n";

		return new Element(
			name:     'pre',
			children: [
				new Element(
					name:     'code',
					children: [new Text($code)]
				)
			]
		);
	}
}
