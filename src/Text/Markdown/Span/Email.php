<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

/**
 * Linked email address in angle brackets
 *
 * @example
 * This is my <email@getkirby.com>.
 * This is my <mailto:email@getkirby.com>.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Email extends Span
{
	protected const HOSTNAME = '[a-zA-Z0-9](?:[a-zA-Z0-9-]{0,61}[a-zA-Z0-9])?';
	protected const ADDRESS  = '[a-zA-Z0-9.!#$%&\'*+\/=?^_`{|}~-]++@'
		. self::HOSTNAME . '(?:\.' . self::HOSTNAME . ')*';
	protected const PATTERN  = '/^<((mailto:)?' . self::ADDRESS . ')>/i';

	public static function markers(): array
	{
		return ['<'];
	}

	public function consume(Phrase $phrase): Node|false
	{
		if ($phrase->has('>') === false) {
			return false;
		}

		$matches = $phrase->match(self::PATTERN);

		if ($matches === null) {
			return false;
		}

		$url = $matches[1];

		if (isset($matches[2]) === false) {
			$url = "mailto:$url";
		}

		$phrase->take($matches[0]);

		return new Element(
			name:       'a',
			attributes: ['href' => $url],
			children:   [new Text($matches[1])],
			break:      false
		);
	}
}
