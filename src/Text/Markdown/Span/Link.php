<?php

namespace Kirby\Text\Markdown\Span;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\Parser\Phrase;
use Kirby\Text\Markdown\Span;

/**
 * Inline link
 *
 * Markdown supports two style of links: inline and reference.
 * In both styles, the link text is delimited by [square brackets].
 * To create an inline link, use a set of regular parentheses immediately
 * after the link text’s closing square bracket. Inside the parentheses,
 * put the URL where you want the link to point, along with an optional
 * title for the link, surrounded in quotes.
 * Reference-style links use a second set of square brackets,
 * inside which you place a label of your choosing to identify the link.
 *
 * @example
 * This is [an example](http://example.com/ "Title") inline link.
 * [This link](http://example.net/){#id .class} has no title attribute.
 * This is [an example][id] reference-style link.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Link extends Span
{
	public static function markers(): array
	{
		return ['['];
	}

	public function consume(Phrase $phrase): Node|false|null
	{
		$remainder = $phrase->text();

		// bail before allocating the element if there is no bracketed
		// link text at the marker at all (common `[` in plain prose)
		if (preg_match('/\[((?:[^][]++|(?R))*+)\]/', $remainder, $matches) !== 1) {
			return false;
		}

		$element = new Element(
			name:       'a',
			attributes: ['href' => null, 'title' => null],
			multiline:  true,
			break:      false,
			content:    $matches[1],
			omit:       [Url::class, Link::class]
		);

		$extent    = strlen($matches[0]);
		$remainder = substr($remainder, $extent);

		if (preg_match('/^[(]\s*+((?:[^ ()]++|[(][^ )]+[)])++)(?:[ ]+("[^"]*+"|\'[^\']*+\'))?\s*+[)]/', $remainder, $matches) === 1) {
			$element->attributes['href'] = $matches[1];

			if (isset($matches[2]) === true) {
				$element->attributes['title'] = substr($matches[2], 1, -1);
			}

			$extent += strlen($matches[0]);
		} else {
			if (preg_match('/^\s*\[(.*?)\]/', $remainder, $matches) === 1) {
				$definition = strlen($matches[1]) > 0 ? $matches[1] : $element->content;
				$definition = strtolower($definition);

				$extent += strlen($matches[0]);
			} else {
				$definition = strtolower($element->content);
			}

			$reference = $this->data()->get('Reference', $definition);

			if ($reference === null) {
				return false;
			}

			$element->attributes['href']  = $reference['url'];
			$element->attributes['title'] = $reference['title'];
		}

		$phrase->take($extent);

		return $this->attributesFromPhrase($element, $phrase);
	}
}
