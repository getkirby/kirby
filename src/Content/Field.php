<?php

namespace Kirby\Content;

use Closure;
use Exception;

/**
 * @package   Kirby Content
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Field
{

    /**
     * The name of the field
     *
     * @var string
     */
    protected $key;

    /**
     * The field value
     *
     * @var mixed
     */
    protected $value;

    /**
     * Registered field methods
     *
     * @var array
     */
    protected $methods = [];

    /**
     * @param string $key   The field name
     * @param string $value The field content
     */
    public function __construct(string $key, string $value)
    {
        $this->key   = $key;
        $this->value = $value;
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

    /**
     * Registers a new field method
     *
     * @param  string|array  $name
     * @param  Closure|null  $method
     * @return Field
     */
    public function method($name, Closure $method = null): Field
    {
        if (is_array($name) === true) {
            foreach ($name as $n => $m) {
                $this->method($n, $m);
            }
            return $this;
        }

        if ($method === null) {
            throw new Exception('Please pass a valid field method closure');
        }

        $this->methods[$name] = $method;
        return $this;
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
        if (isset($this->methods[$method]) === false) {
            throw new Exception('The field method: ' . $method . ' is not available');
        }

        return $this->methods[$method]->call($this, $args);
    }

    /**
     * Magic caller
     *
     * @return mixed
     */
    public function __call(string $method, array $args = [])
    {
        return $this->call($method, $args);
    }

    /**
     * Returns the field value string
     *
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->value;
    }
}
