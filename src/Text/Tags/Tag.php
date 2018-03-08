<?php

namespace Kirby\Text\Tags;

/**
 * The Tag class is the foundation
 * for all tag handling classes, which will
 * be used by the Parser class to parse
 * tags in text.
 *
 * Tag subclasses define the list of allowed
 * attributes and determin how to render the
 * html for the tag.
 *
 * @package   Kirby Text
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
abstract class Tag
{

    /**
     * A list of all detected attributes
     * in the tag
     *
     * @var array
     */
    protected $attributes = [];

    /**
     * An array of injected data
     * which can be used to handle
     * tag dependencies
     *
     * @var array
     */
    protected $data = [];

    /**
     * The main value of the tag.
     * I.e. in the tag (image: myimage.jpg alt: my image)
     * the main value is myimage.jpg
     *
     * @var string
     */
    protected $value;

    /**
     * Returns a list of allowed attributes.
     * This has to be defined by subclasses
     * if attributes should be allowed. It always
     * has to return a flat array of attribute names
     * in lowercase
     *
     * @return array
     */
    public function attributes(): array
    {
        return [];
    }

    /**
     * Injected tag data
     *
     * @return array
     */
    public function data(): array
    {
        return $this->data;
    }

    /**
     * Returns the main value of the tag
     *
     * @return string
     */
    public function value(): string
    {
        return $this->value;
    }

    /**
     * Returns a specific attribute from the
     * list of found attributes.
     *
     * @param  string $name    The name of the attribute
     * @param  mixed  $default Optional default value if the attribute
     *                         could not be found
     * @return mixed
     */
    public function attr(string $name, $default = '')
    {
        return $this->attributes[strtolower($name)] ?? $default;
    }

    /**
     * This is the heart and soul of the tag parser.
     * This method must return the full HTML for the tag.
     *
     * @return string
     */
    protected function html(): string
    {
        return '';
    }

    /**
     * Returns the full HTML for the tag.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->html();
    }

    /**
     * Takes the main value and the list of found
     * attributes and then tries to generate useful
     * html for it with the html method.
     *
     * @param  string $value
     * @param  array  $attributes
     * @param  array  $data
     * @return string
     */
    public function parse(string $value, array $attributes = [], array $data = []): string
    {
        $this->value      = $value;
        $this->attributes = array_change_key_case($attributes);
        $this->data       = $data;

        return $this->html();
    }
}
