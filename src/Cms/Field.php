<?php

namespace Kirby\Cms;

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
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Field
{
    /**
     * Field method aliases
     *
     * @var array
     */
    public static $aliases = [];

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
     * @var Model
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
        $method = strtolower($method);

        if (isset(static::$methods[$method]) === true) {
            return static::$methods[$method](clone $this, ...$arguments);
        }

        if (isset(static::$aliases[$method]) === true) {
            $method = strtolower(static::$aliases[$method]);

            if (isset(static::$methods[$method]) === true) {
                return static::$methods[$method](clone $this, ...$arguments);
            }
        }

        return $this;
    }

    /**
     * Creates a new field object
     *
     * @param object|null $parent
     * @param string $key
     * @param mixed $value
     */
    public function __construct(?object $parent, string $key, $value)
    {
        $this->key    = $key;
        $this->value  = $value;
        $this->parent = $parent;
    }

    /**
     * Simplifies the var_dump result
     *
     * @see Field::toArray
     * @return array
     */
    public function __debugInfo()
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
     * Checks if the field exists in the content data array
     *
     * @return bool
     */
    public function exists(): bool
    {
        return $this->parent->content()->has($this->key);
    }

    /**
     * Checks if the field content is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->value) === true && in_array($this->value, [0, '0', false], true) === false;
    }

    /**
     * Checks if the field content is not empty
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return $this->isEmpty() === false;
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
     * @see Field::parent()
     * @return \Kirby\Cms\Model|null
     */
    public function model()
    {
        return $this->parent;
    }

    /**
     * Provides a fallback if the field value is empty
     *
     * @param mixed $fallback
     * @return self
     */
    public function or($fallback = null)
    {
        if ($this->isNotEmpty()) {
            return $this;
        }

        if (is_a($fallback, 'Kirby\Cms\Field') === true) {
            return $fallback;
        }

        $field = clone $this;
        $field->value = $fallback;
        return $field;
    }

    /**
     * Returns the parent object of the field
     *
     * @return \Kirby\Cms\Model|null
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
     * Returns the field content. If a new value is passed,
     * the modified field will be returned. Otherwise it
     * will return the field value.
     *
     * @param string|\Closure $value
     * @return mixed
     * @throws \Kirby\Exception\InvalidArgumentException
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
