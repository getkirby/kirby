<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Inline;
use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Raw inline HTML: open and closing tags, comments, processing
 * instructions, declarations and CDATA sections.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class RawHtml extends Inline
{
	protected const string WHITESPACE = '[ \t\r\n]';
	protected const string NAME       = '[a-zA-Z][a-zA-Z0-9-]*+';
	protected const string ATTRIBUTE  =
		'[a-zA-Z_:][a-zA-Z0-9_.:-]*+' .
		'(?:' . self::WHITESPACE . '*+=' . self::WHITESPACE . '*+' .
		'(?:[^ \t\r\n"\'=<>`]++|"[^"]*+"|\'[^\']*+\'))?+';

	protected const string OPEN =
		'/^<' . self::NAME .
		'(?:' . self::WHITESPACE . '++' . self::ATTRIBUTE . ')*+' .
		self::WHITESPACE . '*+\/?>/';
	protected const string CLOSE       = '/^<\/' . self::NAME . self::WHITESPACE . '*+>/';
	protected const string COMMENT     = '/^<!-->|^<!--->|^<!--[\s\S]*?-->/';
	protected const string CDATA       = '/^<!\[CDATA\[[\s\S]*?\]\]>/';
	protected const string DECLARATION = '/^<![a-zA-Z][^>]*+>/';
	protected const string INSTRUCTION = '/^<\?[\s\S]*?\?>/';

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

		$first   = $phrase->at(1);
		$pattern = match (true) {
			$first === '/'                            => self::CLOSE,
			$first === '?'                            => self::INSTRUCTION,
			$first === '!' && $phrase->at(2) === '-'  => self::COMMENT,
			$first === '!' && $phrase->at(2) === '['  => self::CDATA,
			$first === '!'                            => self::DECLARATION,
			default                                   => self::OPEN
		};

		if (($matches = $phrase->match($pattern)) === null) {
			return false;
		}

		$phrase->take($matches[0]);

		return new Html($matches[0], break: false);
	}
}
