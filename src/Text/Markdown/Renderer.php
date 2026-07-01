<?php

namespace Kirby\Text\Markdown;

use Kirby\Text\Markdown\AST\Document;
use Kirby\Text\Markdown\AST\Element;
use Kirby\Text\Markdown\AST\Html;
use Kirby\Text\Markdown\AST\Node;
use Kirby\Text\Markdown\AST\Text;

/**
 * Walks a markdown AST and produces HTML.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 * @since     6.0.0
 */
class Renderer
{
	/**
	 * URL schemes permitted in safe mode
	 * @var list<string>
	 */
	protected const array SAFE_URL_SCHEMES = [
		'http://',
		'https://',
		'ftp://',
		'ftps://',
		'mailto:',
		'tel:',
		'data:image/png;base64,',
		'data:image/gif;base64,',
		'data:image/jpeg;base64,',
		'irc:',
		'ircs:',
		'git:',
		'ssh:',
		'news:',
		'steam:',
	];

	public function __construct(
		protected bool $safe = false
	) {
	}

	protected static function escape(
		string $text,
		bool $allowQuotes = false
	): string {
		return htmlspecialchars($text, $allowQuotes ? ENT_NOQUOTES : ENT_QUOTES, 'UTF-8');
	}

	/**
	 * @param array<string, string|null> $attributes
	 * @return array<string, string|null>
	 */
	protected function filterUnsafeUrlInAttribute(
		array $attributes,
		string $attribute
	): array {
		foreach (static::SAFE_URL_SCHEMES as $scheme) {
			if (static::stringAtStart($attributes[$attribute], $scheme) === true) {
				return $attributes;
			}
		}

		$attributes[$attribute] = str_replace(':', '%3A', $attributes[$attribute]);

		return $attributes;
	}

	/**
	 * Renders a single node to HTML.
	 */
	public function render(Node $node): string
	{
		// ordered by frequency: leaf and element nodes make up the bulk of
		// the tree, the single Document root is matched last
		return match (true) {
			$node instanceof Element  => $this->renderElement($node),
			$node instanceof Text     => static::escape($node->text, true),
			$node instanceof Html     => $this->renderHtml($node),
			$node instanceof Document => $this->renderNodes($node->children)
		};
	}

	protected function renderElement(Element $element): string
	{
		$name       = $element->name;
		$attributes = $element->attributes;

		if ($this->safe === true) {
			$attributes = $this->sanitizeAttributes($name, $attributes);
		}

		$hasName = $name !== null;
		$markup  = '';

		if ($hasName === true) {
			$markup .= '<' . $name;

			foreach ($attributes as $attribute => $value) {
				if ($value === null) {
					continue;
				}

				$markup .= " $attribute=\"" . static::escape($value) . '"';
			}
		}

		$hasContent = $element->children !== null;

		if ($hasContent === true) {
			$markup .= $hasName ? '>' : '';

			if ($element->multiline === true) {
				$markup .= $this->renderNodes($element->children);
			} else {
				foreach ($element->children as $child) {
					$markup .= $this->render($child);
				}
			}

			$markup .= $hasName ? '</' . $name . '>' : '';
		} elseif ($hasName === true) {
			$markup .= ' />';
		}

		return $markup;
	}

	protected function renderHtml(Html $node): string
	{
		if ($this->safe === false || $node->trusted === true) {
			return $node->html;
		}

		return static::escape($node->html, true);
	}

	/**
	 * Renders a list of sibling nodes.
	 *
	 * @param list<Node> $nodes
	 */
	public function renderNodes(array $nodes): string
	{
		$markup = '';
		$break  = true;

		foreach ($nodes as $node) {
			$breakNext = $node->hasBreak();

			// insert line break between two nodes
			// only when both break
			$break   = !$break ? $break : $breakNext;
			$markup .= ($break ? "\n" : '') . $this->render($node);
			$break   = $breakNext;
		}

		$markup .= $break ? "\n" : '';

		return $markup;
	}

	/**
	 * @param array<string, string|null> $attributes
	 * @return array<string, string|null>
	 */
	protected function sanitizeAttributes(
		string|null $name,
		array $attributes
	): array {
		static $safe     = '/^[a-zA-Z0-9][a-zA-Z0-9-_]*+$/';
		static $tag2Attr = [
			'a'   => 'href',
			'img' => 'src',
		];

		if ($name === null) {
			return [];
		}

		if (isset($tag2Attr[$name]) === true) {
			$attributes = $this->filterUnsafeUrlInAttribute($attributes, $tag2Attr[$name]);
		}

		if ($attributes !== []) {
			foreach (array_keys($attributes) as $attr) {
				if (!preg_match($safe, $attr)) {
					unset($attributes[$attr]);
				} elseif (static::stringAtStart($attr, 'on') === true) {
					unset($attributes[$attr]);
				}
			}
		}

		return $attributes;
	}

	protected static function stringAtStart(
		string $string,
		string $needle
	): bool {
		return strncasecmp($string, $needle, strlen($needle)) === 0;
	}
}
