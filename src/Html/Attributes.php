<?php

namespace Kirby\Html;

/**
 * The Attributes class represents
 * a list of Attribute objects for HTML
 * elements. Attributes can be added, removed
 * or inspected. The class also makes sure
 * to create nice and clean HTML for the list
 * of registered attributes.
 *
 * @package   Kirby HTML
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Attributes
{

    /**
     * List of Attribute objects
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * Creates a new Attributes object
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->set($attributes);
    }

    /**
     * Checks if the list of attributes contains
     * a particular attribute by name
     *
     * @param  string $attributeName
     * @return bool
     */
    public function contains(string $attributeName): bool
    {
        return array_key_exists(strtolower($attributeName), $this->attributes) === true;
    }

    /**
     * Returns an Attribute object by name, if it exists.
     * If no name is passed, all registered attributes will
     * be returned as array.
     *
     * @param  string|null          $attributeName
     * @return array|Attribute|null
     */
    public function get(string $attributeName = null)
    {
        if ($attributeName === null) {
            return $this->attributes;
        }

        return $this->attributes[$attributeName] ?? null;
    }

    /**
     * Adds a new attribute to the list
     *
     * @param  string|array $attributeName
     * @param  mixed        $attributeValue
     * @return Attributes
     */
    public function set($attributeName, $attributeValue = null): self
    {
        if (is_array($attributeName) === true) {
            foreach ($attributeName as $attrName => $attrVal) {
                $this->set($attrName, $attrVal);
            }
            return $this;
        }

        // create the Attribute object
        $attribute = new Attribute($attributeName, $attributeValue);

        // add it to the list
        $this->attributes[$attribute->name()] = $attribute;

        // always sort attributes in alphabetical order.
        // makes it easier to test and more predictable to work with.
        ksort($this->attributes);

        return $this;
    }

    /**
     * Removes one or multiple attributes from the list
     *
     * @param  string      $attributeNames
     * @return Attributes
     */
    public function remove(...$attributeNames): self
    {
        foreach ($attributeNames as $attributeName) {
            // sanitize the name first
            $attributeName = strtolower($attributeName);

            // remove it
            unset($this->attributes[$attributeName]);
        }
        return $this;
    }

    /**
     * Converts all Attribute objects to arrays and
     * returns the entire list of attributes as array
     *
     * @return array
     */
    public function toArray(): array
    {
        $attributes = [];

        foreach ($this->attributes as $attribute) {
            $attributes[$attribute->name()] = $attribute->value();
        }

        return $attributes;
    }

    /**
     * Creates the HTML for the entire list of attributes
     *
     * @return string
     */
    public function toHtml(): string
    {
        $html = [];
        foreach ($this->attributes as $attribute) {
            $attributeHtml = $attribute->toHtml();

            if (empty($attributeHtml) === false) {
                $html[] = $attributeHtml;
            }
        }
        return implode(' ', $html);
    }

    /**
     * Alias for Attributes::toHtml()
     *
     * @return string
     */
    public function toString(): string
    {
        return $this->toHtml();
    }

    /**
     * Magic string converter
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toHtml();
    }
}
