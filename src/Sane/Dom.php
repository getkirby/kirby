<?php

namespace Kirby\Sane;

use DOMAttr;
use DOMNode;
use DOMNodeList;
use Kirby\Toolkit\Dom as BaseDom;
use Kirby\Toolkit\Escape;
use Kirby\Toolkit\Str;

class Dom extends BaseDom
{
    /**
     * List of allowed elements
     *
     * @var array
     */
    protected $allowed;

    /**
     * List of disallowed elements
     *
     * @var array
     */
    protected $disallowed;

    /**
     * List of attributes that might contain URLs
     *
     * @var array
     */
    protected $urls;

    /**
     * @param string $html
     * @param array $options
     */
    public function __construct(string $html, array $options = [])
    {
        $this->allowed    = $options['allowed'] ?? [];
        $this->disallowed = $options['disallowed']  ?? [];
        $this->urls       = $options['urls']    ?? [];

        parent::__construct($html);
    }

    /**
     * Check for allowed elements according to the allow list
     *
     * @param \DOMNode $element
     * @param \DOMAttr $attribute
     * @return bool
     */
    public function isAllowedAttribute(DOMNode $element, DOMAttr $attribute): bool
    {
        $allowedAttributes = $this->allowed[$element->tagName];

        if (is_array($allowedAttributes) === false) {
            return false;
        }

        if (in_array($attribute->name, $allowedAttributes) !== true) {
            return false;
        }

        // any kind of javascript instructions will be removed
        if (Str::startsWith($attribute->value, 'javascript:') === true) {
            return false;
        }

        return true;
    }

    /**
     * Checks for allowed elements according to the allow list
     *
     * @param \DOMNode $element
     * @return bool
     */
    public function isAllowedElement(DOMNode $element): bool
    {
        $rule = $this->allowed[$element->tagName] ?? false;
        return $rule !== false ? true : false;
    }

    /**
     * Checks for elements to be removed according to the blocklist
     *
     * @param DOMNode $element
     * @return bool
     */
    public function isDisallowedElement(DOMNode $element): bool
    {
        return in_array($element->tagName, $this->disallowed) === true;
    }

    /**
     * Sanitizes all elements in the DOM
     *
     * @return static
     */
    public function sanitize()
    {
        $this->sanitizeElements($this->query('//*'));
        return $this;
    }

    /**
     * Sanitizes all attributes of the given element
     *
     * @param \DOMNode $element
     * @return static
     */
    public function sanitizeAttributes(DOMNode $element)
    {
        foreach ($element->attributes as $attribute) {
            if ($this->isAllowedAttribute($element, $attribute) === false) {
                $element->removeAttribute($attribute->name);
            } elseif (in_array($attribute->name, $this->urls) === true) {
                // data URIs will be removed
                if (Str::startsWith($attribute->value, 'data:') === true) {
                    $element->removeAttribute($attribute->name);
                } else {
                    // TODO: escape xss attacks in query parameters
                    $attribute->value = Escape::attr($attribute->value);
                }
            } else {
                $attribute->value = Escape::attr($attribute->value);
            }
        }

        return $this;
    }

    /**
     * Sanitizes all given elements in the node list
     *
     * @param \DOMNodeList $elements
     * @return static
     */
    public function sanitizeElements(DOMNodeList $elements)
    {
        for ($x = count($elements); $x >=0; $x--) {
            if ($element = $elements[$x]) {
                if ($this->isDeniedElement($element) === true) {
                    $this->remove($element);
                } elseif ($this->isAllowedElement($element) === false) {
                    $this->unwrap($element);
                } elseif ($element->hasAttributes()) {
                    $this->sanitizeAttributes($element);
                }
            }
        }

        return $this;
    }
}
