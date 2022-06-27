<?php

namespace Kirby\Parsley;

use DOMNode;
use DOMNodeList;
use Kirby\Toolkit\Html;

/**
 * Represents an inline element
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
class Inline
{
    /**
     * @var string
     */
    protected $html = '';

    /**
     * @var array
     */
    protected $marks = [];

    /**
     * @param \DOMNode $node
     * @param array $marks
     */
    public function __construct(DOMNode $node, array $marks = [])
    {
        $this->createMarkRules($marks);
        $this->html = trim(static::parseNode($node, $this->marks));
    }

    /**
     * Loads all mark rules
     *
     * @param array $marks
     * @return array
     */
    protected function createMarkRules(array $marks)
    {
        foreach ($marks as $mark) {
            $this->marks[$mark['tag']] = $mark;
        }

        return $this->marks;
    }

    /**
     * Get all allowed attributes for a DOMNode
     * as clean array
     *
     * @param DOMNode $node
     * @param array $marks
     * @return array
     */
    public static function parseAttrs(DOMNode $node, array $marks = []): array
    {
        $attrs    = [];
        $mark     = $marks[$node->tagName];
        $defaults = $mark['defaults'] ?? [];

        foreach ($mark['attrs'] ?? [] as $attr) {
            if ($node->hasAttribute($attr)) {
                $attrs[$attr] = $node->getAttribute($attr);
            } else {
                $attrs[$attr] = $defaults[$attr] ?? null;
            }
        }

        return $attrs;
    }

    /**
     * Parses all children and creates clean HTML
     * for each of them.
     *
     * @param \DOMNodeList $children
     * @param array $marks
     * @return string
     */
    public static function parseChildren(DOMNodeList $children, array $marks): string
    {
        $html = '';
        foreach ($children as $child) {
            $html .= static::parseNode($child, $marks);
        }
        return $html;
    }

    /**
     * Go through all child elements and create
     * clean inner HTML for them
     *
     * @param DOMNode $node
     * @return string|null
     */
    public static function parseInnerHtml(DOMNode $node, array $marks = []): ?string
    {
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
     *
     * @param \DOMNode $node
     * @param array $marks
     * @return string|null
     */
    public static function parseNode(DOMNode $node, array $marks = []): ?string
    {
        if (is_a($node, 'DOMText') === true) {
            return Html::encode($node->textContent);
        }

        // ignore comments
        if (is_a($node, 'DOMComment') === true) {
            return null;
        }

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

        $innerHtml = static::parseInnerHtml($node, $marks);

        // skip empty paragraphs
        if ($innerHtml === null && $node->tagName === 'p') {
            return null;
        }

        // create the outer html for the element
        return '<' . $node->tagName . Html::attr($attrs, null, ' ') . '>' . $innerHtml . '</' . $node->tagName . '>';
    }

    /**
     * Returns the HTML contents of the element
     *
     * @return string
     */
    public function innerHtml(): string
    {
        return $this->html;
    }
}
