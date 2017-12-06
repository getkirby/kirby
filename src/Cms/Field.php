<?php

namespace Kirby\Cms;

use Closure;
use Exception;

class Field
{

    protected static $methods = [];

    protected $key;
    protected $value;
    protected $parent;

    public function __construct(string $key, $value, Object $parent = null)
    {
        $this->key    = $key;
        $this->value  = $value;
        $this->parent = $parent;
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
     * Returns the parent object of the field
     *
     * @return Page|File|Site|User
     */
    public function parent()
    {
        return $this->parent;
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
