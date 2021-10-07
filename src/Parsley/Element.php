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
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Element
{
    /**
     * @var array
     */
    protected $marks;

    /**
     * @var \DOMElement
     */
    protected $node;

    /**
     * @param \DOMElement $node
     * @param array $marks
     */
    public function __construct(DOMElement $node, array $marks = [])
    {
        $this->marks = $marks;
        $this->node  = $node;
    }

    /**
     * The returns the attribute value or
     * the given fallback if the attribute does not exist
     *
     * @param string $attr
     * @param string|null $fallback
     * @return string|null
     */
    public function attr(string $attr, string $fallback = null): ?string
    {
        if ($this->node->hasAttribute($attr)) {
            return $this->node->getAttribute($attr) ?? $fallback;
        }

        return $fallback;
    }

    /**
     * Returns a list of all child elements
     *
     * @return \DOMNodeList
     */
    public function children(): DOMNodeList
    {
        return $this->node->childNodes;
    }

    /**
     * Returns an array with all class names
     *
     * @return array
     */
    public function classList(): array
    {
        return Str::split($this->className(), ' ');
    }

    /**
     * Returns the value of the class attribute
     *
     * @return string|null
     */
    public function className(): ?string
    {
        return $this->attr('class');
    }

    /**
     * Returns the original dom element
     *
     * @return \DOMElement
     */
    public function element()
    {
        return $this->node;
    }

    /**
     * Returns an array with all nested elements
     * that could be found for the given query
     *
     * @param string $query
     * @return array
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
     *
     * @param string $query
     * @return \Kirby\Parsley\Element|null
     */
    public function find(string $query)
    {
        if ($result = $this->query($query)[0]) {
            return new static($result);
        }

        return null;
    }

    /**
     * Returns the inner HTML of the element
     *
     * @param array|null $marks List of allowed marks
     * @return string
     */
    public function innerHtml(array $marks = null): string
    {
        return (new Inline($this->node, $marks ?? $this->marks))->innerHtml();
    }

    /**
     * Returns the contents as plain text
     *
     * @return string
     */
    public function innerText(): string
    {
        return trim($this->node->textContent);
    }

    /**
     * Returns the full HTML for the element
     *
     * @param array|null $marks
     * @return string
     */
    public function outerHtml(array $marks = null): string
    {
        return $this->node->ownerDocument->saveHtml($this->node);
    }

    /**
     * Searches nested elements
     *
     * @param string $query
     * @return DOMNodeList|null
     */
    public function query(string $query)
    {
        return (new DOMXPath($this->node->ownerDocument))->query($query, $this->node);
    }

    /**
     * Removes the element from the DOM
     *
     * @return void
     */
    public function remove()
    {
        $this->node->parentNode->removeChild($this->node);
    }

    /**
     * Returns the name of the element
     *
     * @return string
     */
    public function tagName(): string
    {
        return $this->node->tagName;
    }
}
