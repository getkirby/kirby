<?php

namespace Kirby\Text\Markdown\Block;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;
use Kirby\Text\Markdown\Parser\Line;

/**
 * Fenced code block
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
class FencedCode extends LeafBlock
{
	public static function markers(): array
	{
		return ['`', '~'];
	}

	/**
	 * Derives the `<code>` attributes from a fence's info string.
	 */
	protected function attributes(string $info): array
	{
		if ($info === '') {
			return [];
		}

		$language = substr($info, 0, strcspn($info, " \t\n\f\r"));

		if (str_contains($language, '\\') === true) {
			$language = preg_replace('/\\\\([!-\/:-@\[-`{-~])/', '$1', $language);
		}

		if (str_contains($language, '&') === true) {
			$language = html_entity_decode($language, ENT_QUOTES | ENT_HTML5, 'UTF-8');
		}

		return ['class' => 'language-' . $language];
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

		$info = trim($line->text($length), "\t ");

		// a backtick fence's info string may not contain a backtick
		if ($marker === '`' && str_contains($info, '`') === true) {
			return false;
		}

		$indent     = $line->indent();
		$attributes = $this->attributes($info);

		// take the opening fence line, then read until the closing one
		$line->next();

		$code = [];

		while ($line->valid() === true) {
			if (
				// a closing fence begins with the fence marker;
				// cheapest gate first
				$line->marker() === $marker &&
				$line->isBlank() === false &&
				$line->indent() < 4 &&
				($end = strspn($line->text(), $marker)) >= $length &&
				rtrim($line->text($end), ' ') === ''
			) {
				$line->next();
				break;
			}

			// each content line keeps its terminating newline (so the block
			// content ends with one), with up to the fence's indentation
			// removed; whitespace-only lines keep their remaining spaces
			$code[] = $line->content(columns: $indent);

			$line->next();
		}

		$code = implode("\n", $code);

		if ($code !== '') {
			$code .= "\n";
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
