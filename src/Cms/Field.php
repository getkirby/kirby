<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Exception\InvalidArgumentException;

/**
 * Every field in a Kirby content text file
 * is being converted into such a Field object.
 *
 * Field methods can be registered for those Field
 * objects, which can then be used to transform or
 * convert the field value. This enables our
 * daisy-chaining API for templates and other components
 *
 * ```php
 * // Page field example with lowercase conversion
 * $page->myField()->lower();
 * ```
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Field
{

    /**
     * The field name
     *
     * @var string
     */
    protected $key;

    /**
     * Registered field methods
     *
     * @var array
     */
    public static $methods = [];

    /**
     * The parent object if available.
     * This will be the page, site, user or file
     * to which the content belongs
     *
     * @var Site|Page|File|User
     */
    protected $parent;

    /**
     * The value of the field
     *
     * @var mixed
     */
    public $value;

    /**
     * Magic caller for field methods
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        if (isset(static::$methods[$method]) === true) {
            return static::$methods[$method]($this, ...$arguments);
        }

        return $this;
    }

    /**
     * Creates a new field object
     *
     * @param object $parent
     * @param string $key
     * @param mixed  $value
     */
    public function __construct($parent = null, string $key, $value)
    {
        $this->key    = $key;
        $this->value  = $value;
        $this->parent = $parent;
    }

    /**
     * Simplifies the var_dump result
     *
     * @see Field::toArray
     * @return void
     */
    public function __debuginfo()
    {
        return $this->toArray();
    }

    /**
     * Makes it possible to simply echo
     * or stringify the entire object
     *
     * @see Field::toString
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }


    /**
     * Returns the name of the field
     *
     * @return string
     */
    public function key(): string
    {
        return $this->key;
    }

    /**
     * Returns the parent object of the field
     *
     * @return Page|File|Site|User
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * Converts the Field object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [$this->key => $this->value];
    }

    /**
     * Returns the field value as string
     *
     * @return string
     */
    public function toString(): string
    {
        return (string)$this->value;
    }

    /**
     * Returns the field content
     *
     * @param  string|Closure  $value
     * @return mixed                    If a new value is passed, the modified
     *                                  field will be returned. Otherwise it
     *                                  will return the field value.
     */
    public function value($value = null)
    {
        if ($value === null) {
            return $this->value;
        }

        if (is_scalar($value)) {
            $value = (string)$value;
        } elseif (is_callable($value)) {
            $value = (string)$value->call($this, $this->value);
        } else {
            throw new InvalidArgumentException('Invalid field value type: ' . gettype($value));
        }

        $clone = clone $this;
        $clone->value = $value;

        return $clone;
    }
}
