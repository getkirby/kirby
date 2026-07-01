<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\Line;

/**
 * ATX heading
 *
 * Atx-style headers use 1-6 hash characters at the start
 * of the line, corresponding to header levels 1-6. Optionally,
 * you may “close” atx-style headers. This is purely cosmetic .
 *
 * @example
 * # This is an H1
 * ## This is an H2 ##
 * ### This is an H3 with ID and class {#id .class}
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class AtxHeading extends Block
{
	public static function markers(): array
	{
		return ['#'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		$level = strspn($line->text(), '#');

		if ($level > 6) {
			return false;
		}

		$text = trim($line->text(), '#');
		$text = trim($text, ' ');

		$element = new Element(
			name:      'h' . $level,
			multiline: true,
			content:   $text
		);

		$line->next();

		return $this->attributes($element, '[ #]*', '[ ]*');
	}
}
