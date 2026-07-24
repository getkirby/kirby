<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\HardBreak;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Inline;
use Kirby\Text\Markdown\Parser\Phrase;

/**
 * Backslash escaped character
 *
 * A `\` followed by any ASCII punctuation character escapes it to its
 * literal self (any other backslash is kept verbatim).
 *
 * @example
 * You are my star \* tonight.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class BackslashEscape extends Inline
{
	/**
	 * The ASCII punctuation characters a backslash may escape.
	 */
	protected const string PUNCTUATION = '!"#$%&\'()*+,-./:;<=>?@[\\]^_`{|}~';

	public static function markers(): array
	{
		return ['\\'];
	}

	public function consume(Phrase $phrase): Node|false
	{
		$char = $phrase->at(1);

		// a backslash before a line break is a hard line break
		if ($char === "\n") {
			$phrase->take(2);
			return new HardBreak();
		}

		if ($char === '' || strpbrk($char, self::PUNCTUATION) === false) {
			return false;
		}

		$phrase->take(2);

		// a plain text leaf so the character is HTML-escaped on render
		// (e.g. `\<` becomes `&lt;`) and not re-parsed as a marker
		return new Text($char, break: false);
	}
}
