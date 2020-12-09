<?php

namespace Kirby\Parsley;

use DOMElement;
use DOMXpath;
use Kirby\Toolkit\Str;

class Element
{
    protected $marks;
    protected $node;

    public function __construct(DOMElement $node, array $marks = [])
    {
        $this->marks = $marks;
        $this->node  = $node;
    }

    public function attr(string $attr, $fallback = null)
    {
        if ($this->node->hasAttribute($attr)) {
            return $this->node->getAttribute($attr) ?? $fallback;
        }

        return $fallback;
    }

    public function children()
    {
        return $this->node->childNodes;
    }

    public function classList(): array
    {
        return Str::split($this->className(), ' ');
    }

    public function className()
    {
        return $this->node->getAttribute('class');
    }

    public function element()
    {
        return $this->node;
    }

    public function filter(string $query)
    {
        $result = [];

        if ($queryResult = $this->query($query)) {
            foreach ($queryResult as $node) {
                $result[] = new static($node);
            }
        }

        return $result;
    }

    public function find(string $query)
    {
        if ($result = $this->query($query)[0]) {
            return new static($result);
        }

        return false;
    }

    public function innerHtml(array $marks = null): string
    {
        return (new Inline($this->node, $marks ?? $this->marks))->innerHtml();
    }

    public function innerText()
    {
        return trim($this->node->textContent);
    }

    public function outerHtml(array $marks = null): string
    {
        return $this->node->ownerDocument->saveHtml($this->node);
    }

    public function query($query)
    {
        return (new DOMXPath($this->node->ownerDocument))->query($query, $this->node);
    }

    public function remove()
    {
        $this->node->parentNode->removeChild($this->node);
    }

    public function tagName(): string
    {
        return $this->node->tagName;
    }
}
