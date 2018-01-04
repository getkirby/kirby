<?php

namespace Kirby\Html;

use Exception;

/**
 * The Attribute class can be
 * used to create key/value pairs, which
 * are easily convertible to useful
 * HTML code for attributes in HTML elements
 *
 * <code>
 *
 * echo new Attribute('href', 'https://getkirby.com')->toHtml()
 * //will create `href="https://getkirby.com"`
 *
 * </code>
 *
 * @package   Kirby HTML
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Attribute
{

    /**
     * The name of the attribute
     *
     * All attribute names will be
     * converted to lowercase
     *
     * @var string
     */
    protected $name;

    /**
     * The attribute value
     *
     * @var string
     */
    protected $value;

    /**
     * Creates a new attribute object
     * by name and value.
     *
     * @param string  $name
     * @param mixed   $value
     */
    public function __construct(string $name, $value = null)
    {
        $this->name($name);
        $this->value($value);
    }

    /**
     * Checks if the attribute value is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->value === null || $this->value === '';
    }

    /**
     * Setter and getter for the attribute name
     * Names are being converted to lowercase
     *
     * @param  string|null       $name
     * @return Attribute|string
     */
    public function name(string $name = null)
    {
        if ($name === null) {
            return $this->name;
        }

        $this->name = strtolower($name);
        return $this;
    }

    /**
     * Setter and getter for the attribute value
     * The value must be a string or convertible
     * to a string.
     *
     * @param  mixed             $value
     * @return Attribute|string
     */
    public function value($value = null)
    {
        if ($value === null) {
            return $this->value;
        }

        if (is_scalar($value) === false) {
            throw new Exception('Invalid Attribute value type');
        }

        $this->value = $value;
        return $this;
    }

    /**
     * Converts the Attribute object
     * to a key/value pair array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [$this->name() => $this->value()];
    }

    /**
     * Creates the HTML code for the attribute
     * i.e. `href="https://getkirby.com"
     *
     * @return string
     */
    public function toHtml(): string
    {
        $name  = $this->name();
        $value = $this->value();

        if (empty($name) === true || $value === false || $value === '' || $value === null) {
            return '';
        }

        if ($value === true) {
            return $this->name();
        }

        return $this->name() . '="' . trim($value) . '"';
    }

    /**
     * Returns the value string of the attribute
     *
     * @return string
     */
    public function toString(): string
    {
        return (string)$this->value();
    }

    /**
     * Magic string converter
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
