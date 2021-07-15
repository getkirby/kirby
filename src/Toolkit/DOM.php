<?php

namespace Kirby\Toolkit;

use DOMDocument;
use DOMXPath;

class DOM
{
    protected $doc;
    protected $body;

    public function __construct(string $html)
    {
        libxml_use_internal_errors(true);

        $this->doc = new DOMDocument();
        $this->doc->preserveWhiteSpace = false;
        $this->doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));

        libxml_clear_errors();
    }

    public function body()
    {
        return $this->body = $this->body ?? $this->query('/html/body')[0];
    }

    public function document()
    {
        return $this->doc;
    }

    public function innerHTML($element)
    {
        if ($element === null) {
            return '';
        }

        $html     = '';
        $children = $element->childNodes;

        foreach ($children as $child) {
            $html .= $element->ownerDocument->saveHTML($child);
        }

        return $html;
    }

    public function remove($element)
    {
        $element->parentNode->removeChild($element);
    }

    public function query($query, $element = null)
    {
        $element = $element ?? $this->doc;
        return (new DOMXPath($element))->query($query);
    }

    public function toString(): string
    {
        return $this->doc->saveHTML();
    }

    public function unwrap($element)
    {
        foreach ($element->childNodes as $childNode) {
            $element->parentNode->insertBefore(clone $childNode, $element);
        }

        $this->remove($element);
    }

}
