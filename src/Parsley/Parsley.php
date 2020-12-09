<?php

namespace Kirby\Parsley;

use DOMDocument;
use DOMXPath;
use Kirby\Parsley\Schema\Plain;

class Parsley
{
    protected $blocks = [];
    protected $body;
    protected $doc;
    protected $marks = [];
    protected $nodes = [];
    protected $schema;
    protected $skip = [];

    public static $useXmlExtension = true;

    public function __construct(string $html, Schema $schema = null)
    {
        // fail gracefully if the XML extension is not installed
        // or should be skipped
        if ($this->useXmlExtension() === false) {
            $this->blocks[] = [
                'type' => 'markdown',
                'content' => [
                    'text' => $html,
                ]
            ];
            return;
        }

        libxml_use_internal_errors(true);

        $this->doc = new DOMDocument();
        $this->doc->preserveWhiteSpace = false;
        $this->doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        libxml_clear_errors();

        $this->schema = $schema ?? new Plain();
        $this->skip   = $this->schema->skip();
        $this->marks  = $this->schema->marks();
        $this->inline = [];

        $this->createNodeRules($this->schema->nodes());

        $this->parseNode($this->body());
        $this->endInlineBlock();
    }

    public function blocks(): array
    {
        return $this->blocks;
    }

    public function body()
    {
        return $this->body = $this->body ?? $this->query($this->doc, '/html/body')[0];
    }

    public function createNodeRules($nodes)
    {
        foreach ($nodes as $node) {
            $this->nodes[$node['tag']] = $node;
        }
    }

    public function containsBlock($element): bool
    {
        if (!$element->childNodes) {
            return false;
        }

        foreach ($element->childNodes as $childNode) {
            if ($this->isBlock($childNode) === true || $this->containsBlock($childNode)) {
                return true;
            }
        }

        return false;
    }

    public function endInlineBlock()
    {
        $html = [];

        foreach ($this->inline as $inline) {
            $node = new Inline($inline, $this->marks);
            $html[] = $node->innerHTML();
        }

        $innerHTML = implode(' ', $html);

        if ($fallback = $this->fallback($innerHTML)) {
            $this->mergeOrAppend($fallback);
        }

        $this->inline = [];
    }

    public function fallback($node)
    {
        if (is_a($node, 'DOMText') === true) {
            $html = $node->textContent;
        } elseif (is_a($node, Element::class) === true) {
            $html = $node->innerHtml();
        } elseif (is_string($node) === true) {
            $html = $node;
        } else {
            $html = '';
        }

        if ($fallback = $this->schema->fallback($html)) {
            return $fallback;
        }

        return false;
    }

    public function isBlock($element): bool
    {
        if (is_a($element, 'DOMElement') === false) {
            return false;
        }

        return array_key_exists($element->tagName, $this->nodes) === true;
    }

    public function isInline($element)
    {
        if (is_a($element, 'DOMText') === true) {
            return true;
        }

        if (is_a($element, 'DOMElement') === true) {
            if ($this->containsBlock($element) === true) {
                return false;
            }

            if ($element->tagName === 'p') {
                return false;
            }

            $marks = array_column($this->marks, 'tag');
            return in_array($element->tagName, $marks);
        }

        return false;
    }

    public function mergeOrAppend($block)
    {
        $lastIndex = count($this->blocks) - 1;
        $lastItem  = $this->blocks[$lastIndex] ?? null;

        // merge with previous block
        if ($block['type'] === 'text' && $lastItem && $lastItem['type'] === 'text') {
            $this->blocks[$lastIndex]['content']['text'] .= "\n\n" . $block['content']['text'];

        // append
        } else {
            $this->blocks[] = $block;
        }
    }

    public function parseNode($element)
    {
        // comments
        if (is_a($element, 'DOMComment') === true) {
            return true;
        }


        // inline context
        if ($this->isInline($element)) {
            $this->inline[] = $element;
            return true;
        } else {
            $this->endInlineBlock();
        }

        // known block nodes
        if ($this->isBlock($element) === true) {
            if ($parser = ($this->nodes[$element->tagName]['parse'] ?? null)) {
                if ($result = $parser(new Element($element, $this->marks))) {
                    $this->blocks[] = $result;
                }
            }
            return true;
        }

        // has only unkown children (div, etc.)
        if ($this->containsBlock($element) === false) {
            if (in_array($element->tagName, $this->skip) === true) {
                return true;
            }

            if ($element->tagName !== 'body') {
                $node = new Element($element, $this->marks);

                if ($block = $this->fallback($node)) {
                    $this->mergeOrAppend($block);
                }

                return true;
            }
        }

        // parse all children
        foreach ($element->childNodes as $childNode) {
            $this->parseNode($childNode);
        }
    }

    public function query($element, $query)
    {
        return (new DOMXPath($element))->query($query);
    }

    public function useXmlExtension(): bool
    {
        if (static::$useXmlExtension !== true) {
            return false;
        }

        return class_exists('DOMDocument') === true;
    }
}
