<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Block;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Fenced code block
 *
 * Fenced code blocks are not indented but instead start
 * with a line containing three or more tilde ~ or backtick `
 * characters, and end with a line with the same number of characters.
 *
 * @example
 * ~~~~~~~~~~~~~~~~~~~~~
 * a one-line code block
 * ~~~~~~~~~~~~~~~~~~~~~
 *
 * ```
 * another code block
 * ```
 *
 * ```php
 * // an info string; its first word becomes a `language-php` class
 * echo 'hello';
 * ```
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class FencedCode extends Block
{
	public static function markers(): array
	{
		return ['`', '~'];
	}

	public function consume(
		Line $line,
		Element|null $paragraph = null
	): Node|false {
		$marker = $line->marker();
		$length = strspn($line->text(), $marker);

		if ($length < 3) {
			return false;
		}

		$info = trim($line->slice($length), "\t ");

		if (str_contains($info, '`') === true) {
			return false;
		}

		$attributes = [];

		if ($info !== '') {
			$language   = substr($info, 0, strcspn($info, " \t\n\f\r"));
			$attributes = ['class' => 'language-' . $language];
		}

		// take the opening fence line, then read until the closing one
		$line->next();

		$code   = '';
		$closed = false;

		while ($line->valid() === true) {
			if (
				$line->isBlank() === false &&
				($end = strspn($line->text(), $marker)) >= $length &&
				rtrim($line->slice($end), ' ') === ''
			) {
				$closed = true;
				$line->next();
				break;
			}

			$code .= "\n" . ($line->isBlank() === true ? '' : $line->body());
			$line->next();
		}

		if ($closed === true) {
			$code = substr($code, 1);
		}

		return new Element(
			name:     'pre',
			children: [new Element(
				name:       'code',
				attributes: $attributes,
				children:   [new Text($code)]
			)]
		);
	}
}
