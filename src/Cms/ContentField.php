<?php

namespace Kirby\Cms;

use Closure;
use Exception;

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
class ContentField
{
    /**
     * The field name
     *
     * @var string
     */
    protected $key;

    /**
     * All registered field methods
     *
     * @var array
     */
    protected static $methods = [];

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
    protected $value;

    /**
     * Magic caller
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $args = [])
    {
        // field methods
        if ($this->hasMethod($method)) {
            return $this->call($method, $args);
        }

        // return an unmodified field otherwise
        return $this;
    }

    /**
     * Creates a new field object
     *
     * @param string $key
     * @param mixed  $value
     * @param Object $parent
     */
    public function __construct(string $key, $value, Object $parent = null)
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
     * Calls a registered method class with the
     * passed arguments
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public function call(string $method, array $args = [])
    {
        return static::$methods[$method]->call($this, ...$args);
    }

    /**
     * Checks if the field has a registered method
     *
     * @param string $method
     * @return boolean
     */
    public function hasMethod(string $method): bool
    {
        return isset(static::$methods[$method]) === true;
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
     * Setter and getter for field methods
     *
     * @param  string|array  $name
     * @param  Closure|null  $method
     * @return Field|null
     */
    static public function method($name, Closure $method = null)
    {
        if ($method === null) {
            return static::$methods[$name] ?? null;
        }

        static::$methods[$name] = $method;
    }

    /**
     * Returns all registered field methods
     *
     * @param  array $methods
     * @return array
     */
    static public function methods(array $methods = null): array
    {
        if ($methods === null) {
            return static::$methods;
        }

        // reset all registerd methods
        static::$methods = [];

        foreach ($methods as $name => $method) {
            static::method($name, $method);
        }

        return static::$methods;
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
        } elseif (is_scalar($value)) {
            $this->value = (string)$value;
            return $this;
        } elseif (is_callable($value)) {
            $this->value = (string)$value->call($this, $this->value);
            return $this;
        }

        throw new Exception('Invalid field value type: ' . gettype($value));
    }

}
