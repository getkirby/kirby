<?php

namespace Kirby\Parsley;

use DOMElement;
use DOMNode;
use DOMNodeList;
use DOMText;
use Kirby\Http\Url;
use Kirby\Toolkit\Html;

/**
 * Represents an inline element in an HTML document
 *
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     3.5.0
 */
class Inline
{
	protected string $html = '';

	/**
	 * @var array<string, array>
	 */
	protected array $marks = [];

	public function __construct(DOMNode $node, array $marks = [])
	{
		$this->createMarkRules($marks);

		$html = static::parseNode($node, $this->marks) ?? '';

		// only trim HTML if it doesn't consist of only spaces
		if (trim($html) !== '') {
			$html = trim($html);
		}

		$this->html = $html;
	}

	/**
	 * Loads all mark rules
	 *
	 * @return array<string, array>
	 */
	protected function createMarkRules(array $marks): array
	{
		foreach ($marks as $mark) {
			$this->marks[$mark['tag']] = $mark;
		}

		return $this->marks;
	}

	/**
	 * Get all allowed attributes for a DOMElement
	 * as clean array
	 *
	 * @return array<string, string|null>
	 */
	public static function parseAttrs(
		DOMElement $node,
		array $marks = []
	): array {
		$attrs    = [];
		$mark     = $marks[$node->tagName];
		$defaults = $mark['defaults'] ?? [];

		foreach ($mark['attrs'] ?? [] as $attr) {
			$value = match ($node->hasAttribute($attr)) {
				true    => $node->getAttribute($attr),
				default => $defaults[$attr] ?? null
			};

			if ($attr === 'href' && Url::hasDangerousScheme($value) === true) {
				continue;
			}

			$attrs[$attr] = $value;
		}

		return $attrs;
	}

	/**
	 * Parses all children and creates clean HTML
	 * for each of them.
	 */
	public static function parseChildren(
		DOMNodeList $children,
		array $marks
	): string {
		$html = '';

		foreach ($children as $child) {
			$html .= static::parseNode($child, $marks);
		}

		return $html;
	}

	/**
	 * Go through all child elements and create
	 * clean inner HTML for them
	 */
	public static function parseInnerHtml(
		DOMElement $node,
		array $marks = []
	): string|null {
		$html = static::parseChildren($node->childNodes, $marks);

		// trim the inner HTML for paragraphs
		if ($node->tagName === 'p') {
			$html = trim($html);
		}

		// return null for empty inner HTML
		if ($html === '') {
			return null;
		}

		return $html;
	}

	/**
	 * Converts the given node to clean HTML
	 */
	public static function parseNode(DOMNode $node, array $marks = []): string|null
	{
		if ($node instanceof DOMText) {
			return Html::encode($node->textContent);
		}

		if ($node instanceof DOMElement) {
			// unknown marks
			if (array_key_exists($node->tagName, $marks) === false) {
				return static::parseChildren($node->childNodes, $marks);
			}

			// collect all allowed attributes
			$attrs = static::parseAttrs($node, $marks);

			// close self-closing elements
			if (Html::isVoid($node->tagName) === true) {
				return '<' . $node->tagName . Html::attr($attrs, null, ' ') . ' />';
			}

			$inner = static::parseInnerHtml($node, $marks);

			// skip empty paragraphs
			if ($inner === null && $node->tagName === 'p') {
				return null;
			}

			// create the outer html for the element
			$html  = '<' . $node->tagName . Html::attr($attrs, null, ' ') . '>';
			$html .= $inner;
			$html .= '</' . $node->tagName . '>';

			return $html;
		}

		return null;
	}

	/**
	 * Returns the HTML contents of the element
	 */
	public function innerHtml(): string
	{
		return $this->html;
	}
}
