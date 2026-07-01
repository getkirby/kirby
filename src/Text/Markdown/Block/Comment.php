<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\Line;

/**
 * HTML comment block
 *
 * @example
 * <!-- This is a comment -->
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Comment extends Block
{
	public static function markers(): array
	{
		return ['<'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		if ($this->parser->safe === true) {
			return false;
		}

		if ($line->startsWith('<!--') === false) {
			return false;
		}

		$html = $line->body();

		// always consume the opening line, then keep reading until the comment
		// is closed; unless it already closed on its opening line
		$line->next();

		if (str_contains($html, '-->') === false) {
			while ($line->valid() === true) {
				if ($line->isBlank() === false) {
					$html .= "\n" . $line->body();

					if ($line->has('-->') === true) {
						$line->next();
						break;
					}
				}

				$line->next();
			}
		}

		return new Html($html, break: true);
	}
}
