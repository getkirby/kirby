<?php

namespace Kirby\Parsley\Schema;

use DOMElement;
use DOMText;
use Kirby\Http\Url;
use Kirby\Parsley\Element;
use Kirby\Sane\Html;
use Kirby\Toolkit\Dom;
use Kirby\Toolkit\Str;

/**
 * The blocks schema definition converts
 * the entire document into blocks for the blocks field
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.5.0
 */
class Blocks extends Plain
{
	public function blockquote(Element $node): array
	{
		$text = [];

		// get all the text for the quote
		foreach ($node->children() as $child) {
			if ($child instanceof DOMText) {
				$text[] = trim($child->textContent);
			}

			if (
				$child instanceof DOMElement &&
				$child->tagName !== 'footer'
			) {
				$element = new Element($child);
				$text[]  = $element->innerHtml($this->marks());
			}
		}

		// filter empty blocks and separate text blocks with breaks
		$text = implode('', array_filter($text));

		// get the citation from the footer
		$citation = $node->find('footer')?->innerHtml($this->marks());

		return [
			'content' => [
				'citation' => $citation,
				'text'     => $text
			],
			'type' => 'quote',
		];
	}

	/**
	 * Creates the fallback block type
	 * if no other block can be found
	 */
	public function fallback(Element|string $element): array|null
	{
		if ($element instanceof Element) {
			$html = $element->innerHtml();

			// wrap the inner HTML in a p tag if it doesn't
			// contain one yet.
			if (Str::contains($html, '<p>') === false) {
				$html = '<p>' . $html . '</p>';
			}
		} else {
			$html = trim($element);

			if ($html === '') {
				return null;
			}

			$html = '<p>' . $html . '</p>';
		}

		return [
			'content' => [
				'text' => $html,
			],
			'type' => 'text',
		];
	}

	/**
	 * Converts a heading element to a heading block
	 */
	public function heading(Element $node): array
	{
		$content = [
			'level' => strtolower($node->tagName()),
			'text'  => $node->innerHtml()
		];

		if ($id = $node->attr('id')) {
			$content['id'] = $id;
		}

		ksort($content);

		return [
			'content' => $content,
			'type'    => 'heading',
		];
	}

	public function iframe(Element $node): array|null
	{
		$src        = $node->attr('src') ?? '';
		$figcaption = $node->find('ancestor::figure[1]//figcaption');
		$caption    = $figcaption?->innerHtml($this->marks());

		// avoid parsing the caption twice
		$figcaption?->remove();

		// reverse engineer video URLs
		if (preg_match('!player.vimeo.com\/video\/([0-9]+)!i', $src, $matches) === 1) {
			$url = 'https://vimeo.com/' . $matches[1];
		} elseif (preg_match('!youtube.com\/embed\/([a-zA-Z0-9_-]+)!', $src, $matches) === 1) {
			$url = 'https://youtube.com/watch?v=' . $matches[1];
		} elseif (preg_match('!youtube-nocookie.com\/embed\/([a-zA-Z0-9_-]+)!', $src, $matches) === 1) {
			$url = 'https://youtube.com/watch?v=' . $matches[1];
		} else {
			// only convert iframes with a known video URL; any other
			// iframe is dropped as it can't be stored safely
			return null;
		}

		return [
			'content' => [
				'caption' => $caption,
				'url'     => $url
			],
			'type' => 'video',
		];
	}

	public function img(Element $node): array
	{
		$link       = $node->find('ancestor::a')?->attr('href');
		$figcaption = $node->find('ancestor::figure[1]//figcaption');
		$caption    = $figcaption?->innerHtml($this->marks());

		if (Url::hasDangerousScheme($link) === true) {
			$link = null;
		}

		// avoid parsing the caption twice
		$figcaption?->remove();

		return [
			'content' => [
				'alt'      => $node->attr('alt'),
				'caption'  => $caption,
				'link'     => $link,
				'location' => 'web',
				'src'      => $node->attr('src'),
			],
			'type' => 'image',
		];
	}

	/**
	 * Converts a list element to HTML
	 */
	public function list(Element $node): string
	{
		$html = [];

		foreach ($node->filter('li') as $li) {
			$inner = '';

			foreach ($li->children() as $child) {
				if ($child instanceof DOMText) {
					$inner .= $child->textContent;
				} elseif ($child instanceof DOMElement) {
					$child  = new Element($child);
					$inner .= match (in_array($child->tagName(), ['ul', 'ol'], true)) {
						true    => $this->list($child),
						default => $child->innerHtml($this->marks())
					};
				}
			}

			$html[] = '<li>' . trim($inner) . '</li>';
		}

		return '<' . $node->tagName() . '>' . implode($html) . '</' . $node->tagName() . '>';
	}

	/**
	 * Returns a list of allowed inline marks
	 * and their parsing rules
	 *
	 * @return list<array>
	 */
	public function marks(): array
	{
		return [
			[
				'tag' => 'a',
				'attrs' => ['href', 'rel', 'target', 'title'],
				'defaults' => [
					'rel' => 'noreferrer'
				]
			],
			[
				'tag' => 'abbr',
			],
			[
				'tag' => 'b'
			],
			[
				'tag' => 'br',
			],
			[
				'tag' => 'code'
			],
			[
				'tag' => 'del',
			],
			[
				'tag' => 'em',
			],
			[
				'tag' => 'i',
			],
			[
				'tag' => 'p',
			],
			[
				'tag' => 'strike',
			],
			[
				'tag' => 'sub',
			],
			[
				'tag' => 'sup',
			],
			[
				'tag' => 'strong',
			],
			[
				'tag' => 'u',
			],
		];
	}

	/**
	 * Returns a list of allowed nodes and
	 * their parsing rules
	 *
	 * @codeCoverageIgnore
	 * @return list<array>
	 */
	public function nodes(): array
	{
		return [
			[
				'tag' => 'blockquote',
				'parse' => fn (Element $node) => $this->blockquote($node)
			],
			[
				'tag' => 'h1',
				'parse' => fn (Element $node) => $this->heading($node)
			],
			[
				'tag' => 'h2',
				'parse' => fn (Element $node) => $this->heading($node)
			],
			[
				'tag' => 'h3',
				'parse' => fn (Element $node) => $this->heading($node)
			],
			[
				'tag' => 'h4',
				'parse' => fn (Element $node) => $this->heading($node)
			],
			[
				'tag' => 'h5',
				'parse' => fn (Element $node) => $this->heading($node)
			],
			[
				'tag' => 'h6',
				'parse' => fn (Element $node) => $this->heading($node)
			],
			[
				'tag' => 'hr',
				'parse' => fn (Element $node) => ['type' => 'line']
			],
			[
				'tag' => 'iframe',
				'parse' => fn (Element $node) => $this->iframe($node)
			],
			[
				'tag' => 'img',
				'parse' => fn (Element $node) => $this->img($node)
			],
			[
				'tag' => 'ol',
				'parse' => fn (Element $node) => [
					'content' => [
						'text' => $this->list($node)
					],
					'type' => 'list',
				]
			],
			[
				'tag'   => 'pre',
				'parse' => fn (Element $node) => $this->pre($node)
			],
			[
				'tag' => 'table',
				'parse' => fn (Element $node) => $this->table($node)
			],
			[
				'tag' => 'ul',
				'parse' => fn (Element $node) => [
					'content' => [
						'text' => $this->list($node)
					],
					'type' => 'list',
				]
			],
		];
	}

	public function pre(Element $node): array
	{
		$language = 'text';

		if ($code = $node->find('.//code')) {
			foreach ($code->classList() as $className) {
				if (preg_match('!language-(.*?)!', $className)) {
					$language = str_replace('language-', '', $className);
					break;
				}
			}
		}

		return [
			'content' => [
				'code'     => $node->innerText(),
				'language' => $language
			],
			'type' => 'code',
		];
	}

	/**
	 * Sanitizes raw HTML that is stored verbatim in a block
	 * (e.g. the markdown block created from a table) with a
	 * strict allowlist to prevent stored XSS
	 */
	public function sanitize(string $html): string
	{
		$dom = new Dom($html);

		// reuse the options of the HTML sane handler
		$dom->sanitize([
			...Html::options(isExternal: false),
			'allowedTags' => [
				...Html::$allowedTags,
				'caption'  => true,
				'col'      => ['span'],
				'colgroup' => ['span'],
				'img'      => ['alt', 'height', 'src', 'width'],
				'table'    => true,
				'tbody'    => true,
				'td'       => ['colspan', 'headers', 'rowspan'],
				'tfoot'    => true,
				'th'       => ['abbr', 'colspan', 'headers', 'rowspan', 'scope'],
				'thead'    => true,
				'tr'       => true,
			],
		]);

		return $dom->toString();
	}

	public function table(Element $node): array
	{
		return [
			'content' => [
				'text' => $this->sanitize($node->outerHtml()),
			],
			'type' => 'markdown',
		];
	}
}
