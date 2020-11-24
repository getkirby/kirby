<?php

namespace Kirby\Parsley;

class Inline
{
    protected $html = '';
    protected $marks = [];

    public function __construct($node, array $marks = [])
    {
        $this->createMarkRules($marks);
        $this->html = trim($this->parseNode($node));
    }

    public function createMarkRules($marks)
    {
        foreach ($marks as $mark) {
            $this->marks[$mark['tag']] = $mark;
        }
    }

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

    public function parseNode($node)
    {
        $html = '';

        if (is_a($node, 'DOMText') === true) {
            return $node->textContent;
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

            return '<' . $node->tagName . attr($attrs, ' ') . '>' . $this->parseChildren($node->childNodes) . '</' . $node->tagName . '>';
        }

        // unknown marks
        return $this->parseChildren($node->childNodes);
    }

    public function innerHtml()
    {
        return $this->html;
    }
}
