<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Attributes;
use Kirby\Text\Markdown\Parser\Line;

/**
 * ATX heading
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
class AtxHeading extends LeafBlock
{
	public static function markers(): array
	{
		return ['#'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		$text  = $line->text();
		$level = strspn($text, '#');

		if ($level > 6) {
			return false;
		}

		$rest = substr($text, $level);

		// the opening # must be followed by a space,
		// a tab or the end of the line
		if (
			$rest !== '' &&
			$rest[0] !== ' ' &&
			$rest[0] !== "\t"
		) {
			return false;
		}

		$element = new Element(
			name:      'h' . $level,
			multiline: true,
			content:   $rest
		);

		$line->next();

		$element = Attributes::apply($element, '[ #]*', '[ ]*');

		return $this->strip($element);
	}

	/**
	 * Drop the optional closing sequence of #s
	 * as well as surrounding whitespace
	 */
	protected function strip(Element $element): Element
	{
		$element->content = rtrim($element->content, " \t");

		if (str_contains($element->content, '#') === true) {
			$element->content = preg_replace('/(?:^|[ \t])#+$/', '', $element->content);
		}

		$element->content = trim($element->content, " \t");

		return $element;
	}
}
