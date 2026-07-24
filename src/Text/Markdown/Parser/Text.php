<?php

namespace Kirby\Text\Markdown\Parser;

use Kirby\Text\Markdown\AST;

/**
 * Compiles runs of unmarked source text
 * into inline nodes.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Text
{
	public function __construct(
		protected bool $breaks
	) {
	}

	public function build(string $text): AST\Node
	{
		if (str_contains($text, "\n") === false) {
			return new AST\Text($text);
		}

		$lines    = explode("\n", $text);
		$last     = array_key_last($lines);
		$children = [];

		foreach ($lines as $index => $line) {
			// the final segment keeps its text as-is;
			// earlier ones end in a line break,
			// which drops their trailing spaces
			if ($index === $last) {
				if ($line !== '') {
					$children[] = new AST\Text($line);
				}

				break;
			}

			$trimmed = rtrim($line, ' ');

			if ($trimmed !== '') {
				$children[] = new AST\Text($trimmed);
			}

			// two or more trailing spaces (or `breaks` mode)
			//  make it hard
			$children[] = $this->breaks === true ||
				strlen($line) - strlen($trimmed) >= 2
					? new AST\HardBreak()
					: new AST\SoftBreak();
		}

		return new AST\Element(
			name:      null,
			children:  $children,
			multiline: true,
			break:     false
		);
	}
}
