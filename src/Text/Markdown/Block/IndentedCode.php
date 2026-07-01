<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Block;
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
class IndentedCode extends Block
{
	public static function markers(): array
	{
		return [Block::INDENT];
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

		$code        = substr($line->body(), 4);
		$interrupted = 0;
		$line->next();

		while ($line->valid() === true) {
			if ($line->isBlank() === true) {
				$interrupted++;
				$line->next();
				continue;
			}

			// a dedented non-blank line ends the block
			if ($line->indent() < 4) {
				break;
			}

			if ($interrupted > 0) {
				$code       .= str_repeat("\n", $interrupted);
				$interrupted = 0;
			}

			$code .= "\n" . substr($line->body(), 4);
			$line->next();
		}

		return new Element(
			name:     'pre',
			children: [
				new Element(
					name: 'code',
					children: [
						new Text($code)
					]
				)
			]
		);
	}
}
