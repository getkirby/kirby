<?php

namespace Kirby\Parsley\Schema;

use DOMElement;
use DOMText;
use Kirby\Parsley\Element;

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
class Markdown extends Plain
{
	/**
	 * Creates the fallback block type
	 * if no other block can be found
	 */
	public function fallback(Element|string $element): array|null
	{
		if ($element instanceof Element) {
			$html = $element->innerHtml($this->marks());
		} else {
			$html = trim($element ?? '');
		}

		if (empty($html) === true) {
			return null;
		}

		return [
			'content' => $html,
			'type'    => 'paragraph',
		];
	}

	public function heading(Element $element, int $level): array
	{
		return [
			'content' => str_repeat('#', $level) . ' ' . $element->innerHTML(),
			'type'    => 'h' . $level
		];
	}

	public function iframe(Element $node): array
	{
		$src = $node->attr('src');

		// reverse engineer video URLs
		if (preg_match('!player.vimeo.com\/video\/([0-9]+)!i', $src, $array) === 1) {
			$src = 'https://vimeo.com/' . $array[1];
		} elseif (preg_match('!youtube.com\/embed\/([a-zA-Z0-9_-]+)!', $src, $array) === 1) {
			$src = 'https://youtube.com/watch?v=' . $array[1];
		} elseif (preg_match('!youtube-nocookie.com\/embed\/([a-zA-Z0-9_-]+)!', $src, $array) === 1) {
			$src = 'https://youtube.com/watch?v=' . $array[1];
		} else {
			return null;
		}

		return [
			'content' => '(video: ' . $src . ')',
			'type'    => 'video',
		];
	}

	/**
	 * Converts a list element to HTML
	 */
	public function list(Element $node, int $level = 0): string
	{
		$html = [];
		$type = $node->tagName();

		foreach ($node->filter('li') as $index => $li) {
			$innerHtml = '';
			$lead      = $type === 'ul' ? '-' : $index + 1 . '.';
			$lead      = str_repeat('  ', $level) . $lead;

			foreach ($li->children() as $child) {
				if ($child instanceof DOMText) {
					$innerHtml .= $child->textContent;
				} elseif ($child instanceof DOMElement) {
					$child = new Element($child);

					if (in_array($child->tagName(), ['ul', 'ol']) === true) {
						$innerHtml .= PHP_EOL . $this->list($child, $level + 1);
					} else {
						$innerHtml .= $child->innerHTML($this->marks());
					}
				}
			}

			$html[] = $lead . ' ' . trim($innerHtml);
		}

		return implode(PHP_EOL, $html);
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
				],
				'render' => function ($node, $innerHtml, $attrs) {
					return '[' . $innerHtml . '](' . ($attrs['href'] ?? null) . ')';
				}
			],
			[
				'tag'    => 'b',
				'render' => $bold = function ($node, $innerHtml, $attrs) {
					return '**' . $innerHtml . '**';
				}
			],
			[
				'tag' => 'br',
				'render' => function ($node, $innerHtml, $attrs) {
					return '   ' . PHP_EOL;
				}
			],
			[
				'tag'    => 'code',
				'render' => function ($node, $innerHtml, $attrs) {
					return '`' . $innerHtml . '`';
				}
			],
			[
				'tag'    => 'del',
				'render' => $delete = function ($node, $innerHtml) {
					return '~~' . $innerHtml . '~~';
				}
			],
			[
				'tag'    => 'em',
				'render' => $italic = function ($node, $innerHtml) {
					return '*' . $innerHtml . '*';
				}
			],
			[
				'tag'    => 'i',
				'render' => $italic,
			],
			[
				'tag'    => 'strike',
				'render' => $delete
			],
			[
				'tag'    => 'strong',
				'render' => $bold
			]
		];
	}

	public function nodes(): array
	{
		return [
			[
				'tag'   => 'blockquote',
				'parse' => function (Element $element) {
					return [
						'content' => '> ' . $element->innerHTML($this->marks()),
						'type'    => 'blockquote',
					];
				}
			],
			[
				'tag'   => 'h1',
				'parse' => function (Element $element) {
					return $this->heading($element, 1);
				}
			],
			[
				'tag'   => 'h2',
				'parse' => function (Element $element) {
					return $this->heading($element, 2);
				}
			],
			[
				'tag'   => 'h3',
				'parse' => function (Element $element) {
					return $this->heading($element, 3);
				}
			],
			[
				'tag'   => 'h4',
				'parse' => function (Element $element) {
					return $this->heading($element, 4);
				}
			],
			[
				'tag'   => 'h5',
				'parse' => function (Element $element) {
					return $this->heading($element, 5);
				}
			],
			[
				'tag'   => 'h6',
				'parse' => function (Element $element) {
					return $this->heading($element, 6);
				}
			],
			[
				'tag'   => 'hr',
				'parse' => function (Element $element) {
					return [
						'content' => '****',
						'type'    => 'hr'
					];
				}
			],
			[
				'tag'   => 'iframe',
				'parse' => function (Element $element) {
					return $this->iframe($element);
				}
			],
			[
				'tag'   => 'img',
				'parse' => function (Element $element) {
					return [
						'content' => '![' . $element->attr('alt'). '](' . $element->attr('src') . ')',
						'type'    => 'image'
					];
				}
			],
			[
				'tag'   => 'ol',
				'parse' => function (Element $element) {
					return [
						'content' => $this->list($element),
						'type'    => 'ol',
					];
				}
			],
			[
				'tag'   => 'pre',
				'parse' => function (Element $element) {
					return [
						'content' => '```' . PHP_EOL . $element->innerHTML($this->marks()) . PHP_EOL . '```',
						'type'    => 'code',
					];
				}
			],
			[
				'tag'   => 'ul',
				'parse' => function (Element $element) {
					return [
						'content' => $this->list($element),
						'type'    => 'ul',
					];
				}
			],
			[
				'tag'   => 'video',
				'parse' => function (Element $element) {
					// only support video elements with direct src attribute for now
					if ($element->node()->hasAttribute('src') === false) {
						return null;
					}

					return [
						'content' => '<video src="' . $element->attr('src') . '"></video>',
						'type'    => 'video',
					];
				}
			]
		];
	}

}
