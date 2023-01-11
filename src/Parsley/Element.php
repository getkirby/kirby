<?php

namespace Kirby\Parsley;

use DOMElement;
use DOMNodeList;
use DOMXPath;
use Kirby\Toolkit\Str;

/**
 * Represents a block level element
 * in an HTML document
 *
 * @since 3.5.0
 *
 * @package   Kirby Parsley
 * @author    Bastian Allgeier <bastian@getkirby.com>,
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Element
{
	protected array $marks;
	protected DOMElement $node;

	public function __construct(DOMElement $node, array $marks = [])
	{
		$this->marks = $marks;
		$this->node  = $node;
	}

	/**
	 * The returns the attribute value or
	 * the given fallback if the attribute does not exist
	 */
	public function attr(string $attr, string|null $fallback = null): string|null
	{
		if ($this->node->hasAttribute($attr)) {
			return $this->node->getAttribute($attr) ?? $fallback;
		}

		return $fallback;
	}

	/**
	 * Returns a list of all child elements
	 */
	public function children(): DOMNodeList
	{
		return $this->node->childNodes;
	}

	/**
	 * Returns an array with all class names
	 */
	public function classList(): array
	{
		return Str::split($this->className(), ' ');
	}

	/**
	 * Returns the value of the class attribute
	 */
	public function className(): string|null
	{
		return $this->attr('class');
	}

	/**
	 * Returns the original dom element
	 */
	public function element(): DOMElement
	{
		return $this->node;
	}

	/**
	 * Returns an array with all nested elements
	 * that could be found for the given query
	 */
	public function filter(string $query): array
	{
		$result = [];

		if ($queryResult = $this->query($query)) {
			foreach ($queryResult as $node) {
				$result[] = new static($node);
			}
		}

		return $result;
	}

	/**
	 * Tries to find a single nested element by
	 * query and otherwise returns null
	 */
	public function find(string $query): Element|null
	{
		if ($result = $this->query($query)[0]) {
			return new static($result);
		}

		return null;
	}

	/**
	 * Returns the inner HTML of the element
	 */
	public function innerHtml(array|null $marks = null): string
	{
		return (new Inline($this->node, $marks ?? $this->marks))->innerHtml();
	}

	/**
	 * Returns the contents as plain text
	 */
	public function innerText(): string
	{
		return trim($this->node->textContent);
	}

	/**
	 * Returns the full HTML for the element
	 */
	public function outerHtml(array|null $marks = null): string
	{
		return $this->node->ownerDocument->saveHtml($this->node);
	}

	/**
	 * Searches nested elements
	 */
	public function query(string $query): DOMNodeList|null
	{
		$path = new DOMXPath($this->node->ownerDocument);
		return $path->query($query, $this->node);
	}

	/**
	 * Removes the element from the DOM
	 */
	public function remove(): void
	{
		$this->node->parentNode->removeChild($this->node);
	}

	/**
	 * Returns the name of the element
	 */
	public function tagName(): string
	{
		return $this->node->tagName;
	}
}
