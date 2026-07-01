<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Link reference definition
 *
 * Anywhere in the document, you define your link without producing
 * any output itself. The link reference will then be used by the
 * reference syntax of `Kirby\Text\Markdown\Span\Link`
 *
 * @example
 * [id]: http://example.com/  "Optional Title Here"
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Reference extends Block
{
	protected const PATTERN = '/^\[(.+?)\]:[ ]*+<?(\S+?)>?(?:[ ]+["\'(](.+)["\')])?[ ]*+$/';

	public static function markers(): array
	{
		return ['['];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): false|null {
		if ($line->has(']') === false) {
			return false;
		}

		$matches = $line->match(self::PATTERN);

		if ($matches === null) {
			return false;
		}

		$id = strtolower($matches[1]);

		$this->data()->set('Reference', $id, [
			'url'   => $matches[2],
			'title' => $matches[3] ?? null
		]);

		$line->next();

		// the definition itself produces no output
		return null;
	}
}
