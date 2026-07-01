<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\HtmlElements;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

/**
 * Raw inline HTML
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 *
 * @todo Solve with Toolkit\Html instead
 */
class Markup extends Span
{
	protected const CLOSE_PATTERN   = '/^<\/\w[\w-]*+[ ]*+>/s';
	protected const COMMENT_PATTERN = '/^<!---?[^>-](?:-?+[^-])*-->/s';
	protected const TAG_PATTERN     = '/^<\w[\w-]*+(?:[ ]*+' . HtmlElements::ATTRIBUTE_REGEX . ')*+[ ]*+\/?>/s';

	public static function markers(): array
	{
		return ['<'];
	}

	public function consume(Phrase $phrase): Node|false|null
	{
		if (
			$this->parser->safe === true ||
			$phrase->has('>') === false
		) {
			return false;
		}

		// closing tag
		if (
			$phrase->at(1) === '/' &&
			($matches = $phrase->match(self::CLOSE_PATTERN)) !== null
		) {
			$phrase->take($matches[0]);

			return new Html($matches[0], break: false);
		}

		// comment
		if (
			$phrase->at(1) === '!' &&
			($matches = $phrase->match(self::COMMENT_PATTERN)) !== null
		) {
			$phrase->take($matches[0]);

			return new Html($matches[0], break: false);
		}

		// inline HTML tags
		if (
			$phrase->at(1) !== ' ' &&
			($matches = $phrase->match(self::TAG_PATTERN)) !== null
		) {
			$phrase->take($matches[0]);

			return new Html($matches[0], break: false);
		}

		return false;
	}
}
