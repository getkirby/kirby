<?php

namespace Kirby\Parsley;

use DOMNode;
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
 * @copyright Bastian Allgeier GmbH
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
        $this->html = trim($this->parseNode($node));
    }

    /**
     * Loads all mark rules
     *
     * @param array $marks
     * @return array
     */
    public function createMarkRules(array $marks)
    {
        foreach ($marks as $mark) {
            $this->marks[$mark['tag']] = $mark;
        }

        return $this->marks;
    }

    /**
     * Parses all children and creates clean HTML
     * for each of them.
     *
     * @param \DOMNodeList $children
     * @return string
     */
    public function parseChildren($children): string
    {
        if (!$children) {
            return '';
        }

        $html = '';
        foreach ($children as $child) {
            $html .= $this->parseNode($child);
        }
        return $html;
    }

    /**
     * Converts the given node to clean HTML
     *
     * @param \DOMNode $node
     * @return void
     */
    public function parseNode(DOMNode $node)
    {
        if (is_a($node, 'DOMText') === true) {
            return Html::encode($node->textContent);
        }

        // ignore comments
        if (is_a($node, 'DOMComment') === true) {
            return '';
        }

        // known marks
        if (array_key_exists($node->tagName, $this->marks) === true) {
            $mark     = $this->marks[$node->tagName];
            $attrs    = [];
            $defaults = $mark['defaults'] ?? [];

            foreach ($mark['attrs'] ?? [] as $attr) {
                if ($node->hasAttribute($attr)) {
                    $attrs[$attr] = $node->getAttribute($attr);
                } else {
                    $attrs[$attr] = $defaults[$attr] ?? null;
                }
            }

            if (Html::isVoid($node->tagName) === true) {
                return '<' . $node->tagName . attr($attrs, ' ') . ' />';
            }

            return '<' . $node->tagName . attr($attrs, ' ') . '>' . $this->parseChildren($node->childNodes) . '</' . $node->tagName . '>';
        }

        // unknown marks
        return $this->parseChildren($node->childNodes);
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
