<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Setext heading
 *
 * Setext-style headers are “underlined” using equal signs
 * (for first-level headers) and dashes (for second-level headers).
 *
 * @example
 * This is an H1
 * =============
 *
 * This is an H2 {#id .class}
 * -------------
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class SetextHeading extends Block
{
	public static function markers(): array
	{
		return ['=', '-'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		if (
			$paragraph === null ||
			$line->isBlank(offset: -1) === true
		) {
			return false;
		}

		if (
			$line->indent() > 3 ||
			rtrim(rtrim($line->text(), ' '), $line->marker()) !== ''
		) {
			return false;
		}

		$paragraph->name = $line->marker() === '=' ? 'h1' : 'h2';

		// take the underline line
		$line->next();

		return $this->attributes($paragraph, '[ ]*', '[ ]*');
	}
}
