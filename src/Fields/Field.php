<?php

namespace Kirby\Fields;

use Closure;
use Exception;

class Field
{

    protected static $methods = [];

    protected $key;
    protected $value;
    protected $dependencies = [];

    public function __construct(string $key, $value, array $dependencies = [])
    {
        $this->key          = $key;
        $this->value        = $value;
        $this->dependencies = $dependencies;
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
    static public function method($name, Closure $method = null)
    {
        if (is_array($name) === true) {
            foreach ($name as $n => $m) {
                static::method($n, $m);
            }
            return;
        }

        if ($method === null) {
            throw new Exception('Please pass a valid field method closure');
        }

        static::$methods[$name] = $method;
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
     * Magic caller
     *
     * @return mixed
     */
    public function __call(string $method, array $args = [])
    {
        // field methods
        if ($this->hasMethod($method)) {
            return $this->call($method, $args);
        }

        // magic dependency getters
        if (isset($this->dependencies[$method]) === true) {
            return $this->dependencies[$method];
        }

        // return an unmodified field otherwise
        return $this;
    }

    public function toString(): string
    {
        return (string)$this->value;
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function toArray(): array
    {
        return [$this->key => $this->value];
    }

    public function __debuginfo()
    {
        return $this->toArray();
    }

}
