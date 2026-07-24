<?php

namespace Kirby\Text\Markdown\Inline;

use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\Parser\Attributes;
use Kirby\Text\Markdown\Parser\LinkTarget;

/**
 * Inline link
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
class Link extends BracketedInline
{
	/**
	 * The ASCII punctuation kept verbatim in a URL; every other byte
	 * (space, `"`, `<`, `>`, `\`, non-ASCII, …) is percent-encoded.
	 */
	protected const string SAFE = '!#$%&\'()*+,-./:;=?@_~';

	/**
	 * The full set of bytes `href()` keeps verbatim:
	 * URL-safe punctuation plus ASCII alphanumerics.
	 */
	protected const string HREF_SAFE =
		'abcdefghijklmnopqrstuvwxyz' .
		'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
		'0123456789' .
		self::SAFE;

	public static function markers(): array
	{
		return ['['];
	}

	/**
	 * Decodes HTML entities and numeric character references.
	 */
	public static function decode(string $text): string
	{
		return html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
	}

	/**
	 * A link wraps its children in an `<a>`;
	 * any anchor within the text  is flattened away,
	 * since a link may not contain another link.
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $children
	 */
	public function element(array $resolved, array $children): Element
	{
		return new Element(
			name:       'a',
			attributes: $resolved['attributes'] ?? [],
			children:   self::flatten($children),
			multiline:  true,
			break:      false
		);
	}

	/**
	 * Flattens anchors within a link's text
	 * back to their content
	 *
	 * @param list<\Kirby\Text\Markdown\AST\Node> $nodes
	 * @return list<\Kirby\Text\Markdown\AST\Node>
	 */
	protected static function flatten(array $nodes): array
	{
		$flat = [];

		foreach ($nodes as $node) {
			if ($node instanceof Element && $node->children !== null) {
				$children = self::flatten($node->children);

				// unwrap an anchor into its (already flattened) contents
				if ($node->name === 'a') {
					array_push($flat, ...$children);
					continue;
				}

				$node->children = $children;
			}

			$flat[] = $node;
		}

		return $flat;
	}

	/**
	 * Normalizes a link destination: decodes entities, then
	 * percent-encodes every byte outside the URL-safe set.
	 */
	public static function href(string $destination): string
	{
		// entity decoding only ever rewrites `&…;` sequences;
		// skip it when there is no `&` at all
		if (str_contains($destination, '&') === true) {
			$destination = self::decode($destination);
		}

		$length = strlen($destination);

		// when every byte is already URL-safe (common case: a clean URL)
		// return the string unchanged
		if (strspn($destination, self::HREF_SAFE) === $length) {
			return $destination;
		}

		$href = '';

		for ($i = 0; $i < $length; $i++) {
			$char = $destination[$i];
			$byte = ord($char);

			$safe =
				($byte >= 0x30 && $byte <= 0x39) ||
				($byte >= 0x41 && $byte <= 0x5A) ||
				($byte >= 0x61 && $byte <= 0x7A) ||
				($byte < 0x80 && strpbrk($char, self::SAFE) !== false);

			$href .= $safe === true ? $char : '%' . strtoupper(bin2hex($char));
		}

		return $href;
	}

	/**
	 * Normalizes a link label for reference matching.
	 */
	public static function label(string $label): string
	{
		return mb_convert_case(
			preg_replace('/\s+/', ' ', trim($label)),
			MB_CASE_FOLD,
			'UTF-8'
		);
	}

	/**
	 * Parses the destination or reference
	 * that follows a link/image's closing `]`
	 *
	 * @return array{attributes: array<string, string|null>, length: int}|null
	 */
	public function open(string $after, string $label): array|null
	{
		// an inline `(destination "title")` target right after the text,
		// otherwise a reference (`[label]`, collapsed `[]` or shortcut)
		$target = LinkTarget::parse($after) ?? $this->reference($after, $label);

		if ($target === null) {
			return null;
		}

		['href' => $href, 'title' => $title, 'length' => $length] = $target;

		// normalize the destination (entity-decode + percent-encode)
		// and entity-decode the title, per CommonMark
		$attributes = [
			'href'  => self::href((string)$href),
			'title' => $title !== null ? self::decode($title) : null
		];

		// an optional trailing `{#id .class}` attribute block;
		// the pattern is anchored `^[ ]*{`, so only attempt the
		// (costly) grammar match when a `{` actually follows
		// the optional leading spaces
		$rest = substr($after, $length);

		if (
			($rest[strspn($rest, ' ')] ?? '') === '{' &&
			preg_match('/^[ ]*{(' . Attributes::PATTERN . ')}/', $rest, $matches) === 1
		) {
			$attributes += Attributes::parse($matches[1]);
			$length     += strlen($matches[0]);
		}

		return [
			'attributes' => $attributes,
			'length'     => $length
		];
	}

	/**
	 * Resolves a reference that follows the `]`:
	 * a full `[label]`, a collapsed `[]`, shortcut (the link text).
	 *
	 * @return array{href: string, title: string|null, length: int}|null
	 */
	protected function reference(string $after, string $label): array|null
	{
		if (preg_match('/^\[(.*?)\]/', $after, $matches) === 1) {
			$definition = strlen($matches[1]) > 0 ? $matches[1] : $label;
			$length     = strlen($matches[0]);
		} else {
			$definition = $label;
			$length     = 0;
		}

		$label     = self::label($definition);
		$reference = $this->data()->get('LinkDefinition', $label);

		if ($reference === null) {
			return null;
		}

		return [
			'href'   => $reference['url'],
			'title'  => $reference['title'],
			'length' => $length
		];
	}
}
