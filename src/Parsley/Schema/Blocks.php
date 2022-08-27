<?php

namespace Kirby\Parsley\Schema;

use DOMElement;
use DOMText;
use Kirby\Parsley\Element;
use Kirby\Toolkit\Str;

/**
 * The plain schema definition converts
 * the entire document into simple text blocks
 *
 * @since 3.5.0
 *
 * @package   Kirby Parsley
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Blocks extends Plain
{
	public function blockquote(Element $node): array
	{
		$citation = null;
		$text     = [];

		// get all the text for the quote
		foreach ($node->children() as $child) {
			if ($child instanceof DOMText) {
				$text[] = trim($child->textContent);
			}

			if (
				$child instanceof DOMElement &&
				$child->tagName !== 'footer'
			) {
				$text[] = (new Element($child))->innerHTML($this->marks());
			}
		}

		// filter empty blocks and separate text blocks with breaks
		$text = implode('', array_filter($text));

		// get the citation from the footer
		if ($footer = $node->find('footer')) {
			$citation = $footer->innerHTML($this->marks());
		}

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
		} elseif (is_string($element) === true) {
			$html = trim($element);

			if (Str::length($html) === 0) {
				return null;
			}

			$html = '<p>' . $html . '</p>';
		} else {
			return null;
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
			'text'  => $node->innerHTML()
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

	public function iframe(Element $node): array
	{
		$caption = null;
		$src     = $node->attr('src');

		if ($figcaption = $node->find('ancestor::figure[1]//figcaption')) {
			$caption = $figcaption->innerHTML($this->marks());

			// avoid parsing the caption twice
			$figcaption->remove();
		}

		// reverse engineer video URLs
		if (preg_match('!player.vimeo.com\/video\/([0-9]+)!i', $src, $array) === 1) {
			$src = 'https://vimeo.com/' . $array[1];
		} elseif (preg_match('!youtube.com\/embed\/([a-zA-Z0-9_-]+)!', $src, $array) === 1) {
			$src = 'https://youtube.com/watch?v=' . $array[1];
		} elseif (preg_match('!youtube-nocookie.com\/embed\/([a-zA-Z0-9_-]+)!', $src, $array) === 1) {
			$src = 'https://youtube.com/watch?v=' . $array[1];
		} else {
			$src = false;
		}

		// correct video URL
		if ($src) {
			return [
				'content' => [
					'caption' => $caption,
					'url'     => $src
				],
				'type' => 'video',
			];
		}

		return [
			'content' => [
				'text' => $node->outerHTML()
			],
			'type' => 'markdown',
		];
	}

	public function img(Element $node): array
	{
		$caption = null;
		$link = null;

		if ($figcaption = $node->find('ancestor::figure[1]//figcaption')) {
			$caption = $figcaption->innerHTML($this->marks());

			// avoid parsing the caption twice
			$figcaption->remove();
		}

		if ($a = $node->find('ancestor::a')) {
			$link = $a->attr('href');
		}

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
			$innerHtml = '';

			foreach ($li->children() as $child) {
				if ($child instanceof DOMText) {
					$innerHtml .= $child->textContent;
				} elseif ($child instanceof DOMElement) {
					$child = new Element($child);

					if (in_array($child->tagName(), ['ul', 'ol']) === true) {
						$innerHtml .= $this->list($child);
					} else {
						$innerHtml .= $child->innerHTML($this->marks());
					}
				}
			}

			$html[] = '<li>' . trim($innerHtml) . '</li>';
		}

		return '<' . $node->tagName() . '>' . implode($html) . '</' . $node->tagName() . '>';
	}

	/**
	 * Returns a list of allowed inline marks
	 * and their parsing rules
	 */
	public function marks(): array
	{
		return [
			[
				'tag' => 'a',
				'attrs' => ['href', 'rel', 'target', 'title'],
				'defaults' => [
					'rel' => 'noopener noreferrer'
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

		if ($code = $node->find('//code')) {
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

	public function table(Element $node): array
	{
		return [
			'content' => [
				'text' => $node->outerHTML(),
			],
			'type' => 'markdown',
		];
	}
}
