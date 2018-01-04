<?php

namespace Kirby\Html;

use Exception;

/**
 * The Element class is a powerful HTML Element
 * builder, which is similar to jQuery's way
 * of creating DOM elements. You can use it
 * to create new HTML code with a clean chainable
 * API and to inspect and manipulate those elements.
 *
 * You can also extend the Element object to create
 * your own custom Elements, like simple
 * web components without the interactivity.
 *
 * See Kirby\Html\Element\A and Kirby\Html\Element\Img
 * for such custom elements.
 *
 * @package   Kirby Html
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Element
{

    /**
     * List of void elements without
     * a closing tag. This list can
     * be manipulated globally if necessary.
     *
     * @var array
     */
    public static $void = [
      'area',
      'base',
      'br',
      'col',
      'command',
      'embed',
      'hr',
      'img',
      'input',
      'keygen',
      'link',
      'meta',
      'param',
      'source',
      'track',
      'wbr',
    ];

    /**
     * The name of the HTML tag
     *
     * @var string
     */
    protected $tagName;

    /**
     * The innerHTML for the element
     *
     * @var string
     */
    protected $html;

    /**
     * A list of class names, which can
     * be manipulated and inspected via
     * the ClassList object
     *
     * @var ClassList
     */
    protected $classList;

    /**
     * A list of attributes for the Element
     * which can be manipulated and inspected
     * via the Attributes object
     *
     * @var Attributes
     */
    protected $attr;

    /**
     * Creates a new Element
     * The constructor parameters are pretty
     * flexible. You have multiple ways to
     * create the object:
     *
     * 1. `new Element('a')`
     * 2. `new Element('a', 'Some inner HTML', ['rel' => 'me'])`
     * 3. `new Element('a', new Element('span'), ['rel' => 'me'])
     * 4. `new Element('a', [new Element('span'), new Element('span')], ['rel' => 'me'])
     * 5. `new Element('a', ['rel' => 'me'])`
     *
     * @param string               $tagName
     * @param string|Element|array $html
     * @param array                $attr
     */
    public function __construct(string $tagName, $html = '', array $attr = [])
    {
        $this->tagName($tagName);

        $this->classList = new ClassList;
        $this->attr      = new Attributes;

        if (is_array($html) === true) {
            // list of elements
            if (key($html) === 0) {
                $this->html($html);
            } else {
                $attr = $html;
            }
        } else {
            $this->html($html);
        }

        $this->attr($attr);
    }

    /**
     * Multi-purpose element creator.
     * Pass either an element object or
     * a string as first argument
     *
     * @param string|Element $element
     * @param mixed ...$arguments
     * @return self
     */
    public static function factory($element, ...$arguments): self
    {
        if (is_a($element, Element::class)) {
            return $element;
        }

        return new static($element, ...$arguments);
    }

    /**
     * Setter and getter for the tag name
     * Tag names will always be converted
     * to lowercase.
     *
     * @param  string|null     $tagName
     * @return string|Element
     */
    public function tagName()
    {
        $tagName = func_get_args()[0] ?? null;

        if ($tagName === null) {
            return $this->tagName;
        }
        $this->tagName = strtolower($tagName);
        return $this;
    }

    /**
     * Setter and getter for the inner HTML
     *
     * @param  string|array|Element $html
     * @return Element|string
     */
    public function html()
    {
        $html = func_get_args()[0] ?? null;

        if ($html === null) {
            return $this->html;
        }

        if (is_array($html) === true) {
            $html = implode($html);
        } elseif (is_a($html, self::class) === true) {
            $html = $html->toString();
        }

        $this->html = $html;
        return $this;
    }

    /**
     * Setter for the innerText
     * This can be used instead of setting
     * HTML as content. The text will be
     * encoded with htmlentities.
     *
     * @param  string $text
     * @return Element
     */
    public function text(string $text): self
    {
        return $this->html(htmlentities($text, ENT_COMPAT, 'utf-8'));
    }

    /**
     * Checks if the element has a closing tag
     * See `static::$void` for a list of all
     * void elements. You can manipulate this
     * list globally by overwriting `Element::$void`
     * with your own array of tag names.
     *
     * @return boolean
     */
    public function isVoid(): bool
    {
        return in_array($this->tagName(), static::$void) === true;
    }

    /**
     * Returns the class list object, which
     * is being used to manipulate and inspect
     * class selectors for the element.
     *
     * @return ClassList
     */
    public function classList(): ClassList
    {
        return $this->classList;
    }

    /**
     * Setter and getter for element attributes
     *
     * This method has multiple modes:
     *
     * 1. Getter for all attributes:
     * `$element->attr()`
     * Returns the Attributes object
     *
     * 2. Getter for a particular attribute:
     * `$element->attr('rel')`
     * Returns the Attribute object or null
     *
     * 3. Getter for the class list
     * `$element->attr('class')`
     * Returns the ClassList object
     *
     * 4. Setter for a single attribute
     * `$element->attr('rel', 'me')`
     * Sets the rel attribute and returns the modified Element
     *
     * 5. Setter for multiple attributes
     * `$element->attr(['rel' => 'me', 'href' => '#'])`
     * Returns the modified Element
     *
     * 6. Adding a new class selector to the ClassList
     * `$element->attr('class', 'button')`
     * Returns the modified Element
     *
     * @param  string|null|array                      $name
     * @param  mixed
     * @return mixed
     */
    public function attr($name = null, $value = null)
    {
        // Getter for all attributes, Element::attr()
        if ($name === null) {
            return $this->attr;
        }

        //  Setter for multiple attributes, Element::attr([…])
        if (is_array($name) === true) {
            return $this->setAttributes($name);
        }

        // Getter for a particular attribute, Element::attr('…')
        if ($value === null) {
            return $name === 'class' ? $this->classList() : $this->attr->get($name);
        }

        // Adding a new selector to the ClassList, Element::attr('class', '…')
        if ($name === 'class') {
            return $this->setAttributeClass($value);
        }

        // Setter for single attribute, Element::attr('…', '–')
        $this->attr->set($name, $value);
        return $this;
    }

    /**
     * Helper for seting multiple attributes
     *
     * @param  array   $attributes
     * @return Element
     */
    protected function setAttributes(array $attributes): self
    {
        foreach ($attributes as $n => $v) {
            $this->attr($n, $v);
        }
        return $this;
    }

    /**
     * Helper to add a new class selector to the ClassList
     *
     * @param  mixed   $value
     * @return Element
     */
    protected function setAttributeClass($value): self
    {
        $this->classList = new ClassList;
        $this->classList->add($value);
        return $this;
    }

    /**
     * Checks if the ClassList contains the given className
     *
     * @param  string  $className
     * @return boolean
     */
    public function hasClass(string $className): bool
    {
        return $this->classList()->contains($className) === true;
    }

    /**
     * Adds a new className or multiple
     * classNames to the ClassList
     *
     * 1. single className:
     * `$element->addClass('button')`
     *
     * 2. multiple classNames:
     * `$element->addClass('button', 'button-primary')`
     *
     * @param  array|string|list $classNames
     * @return Element
     */
    public function addClass(...$classNames): self
    {
        $this->classList()->add(...$classNames);
        return $this;
    }

    /**
     * Adds the given className if it doesn't
     * exist yet, or otherwise removes it from
     * the ClassList
     *
     * @param  string $className
     * @return Element
     */
    public function toggleClass(string $className): self
    {
        $this->classList()->toggle($className);
        return $this;
    }

    /**
     * Removes one or many classNames from the ClassList
     *
     * 1. single className:
     * `$element->removeClass('button')`
     *
     * 2. multiple classNames:
     * `$element->removeClass('button', 'button-primary')`
     *
     * @param  string|list $classNames
     * @return Element
     */
    public function removeClass(string ...$classNames): self
    {
        $this->classList()->remove(...$classNames);
        return $this;
    }

    /**
     * Returns the opening tag for the element
     *
     * @return string
     */
    public function begin(): string
    {
        // get all attributes
        $attributes = $this->attr;

        // add all css class selectors
        $attributes->set('class', $this->classList()->toString());

        // get all attributes
        $attr = $attributes->toHtml();

        // start the html tag
        $html = '<' . $this->tagName();

        // add the attributes
        if (empty($attr) === false) {
            $html .= ' ' . $attr;
        }

        // close the starting tag
        $html .= '>';

        // return the starting tag
        return $html;
    }

    /**
     * Returns the closing tag for the element
     * if it's not a void element
     *
     * @return string
     */
    public function end(): string
    {
        // add the rest of the tag for non-void tags
        if ($this->isVoid() === false) {
            return '</' . $this->tagName() . '>';
        }

        return '';
    }

    /**
     * Wraps the Element into another one
     * The new parent Element will be returned
     *
     * There are two ways to define wrapping
     * elements:
     *
     * 1. A simple string
     * `$element->wrap('div')`
     *
     * 2. A parent Element
     * `$element->wrap(new Element('div'))`
     *
     * @param  string|Element $element
     * @return Element
     */
    public function wrap($element): self
    {
        if (is_string($element) === true) {
            $element = new Element($element);
        } elseif (is_a($element, self::class) === false) {
            throw new Exception('Invalid wrapper element. Must be an extension of the Kirby\\Html\\Element class');
        }

        return $element->html($this);
    }

    /**
     * Converts the Element to a string
     * and renders it with all attributes
     * and class names.
     *
     * @return string
     */
    public function toHtml(): string
    {
        $html = $this->begin();

        // add the rest of the tag for non-void tags
        if ($this->isVoid() === false) {
            $html .= $this->html();
            $html .= $this->end();
        }

        return $html;
    }

    /**
     * See Element::toHtml
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Magic string converter:
     *
     * `echo new Element('div')`
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
