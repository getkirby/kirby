<?php

namespace Kirby\Fields;

use Closure;
use Exception;

class Field
{

    protected static $methods = [];

    protected $key;
    protected $value;

    public function __construct(string $key, $value)
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
     * Calls a registered method class with the
     * passed arguments
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public function call(string $method, array $args = [])
    {
        if (isset(static::$methods[$method]) === false) {
            return $this;
        }

        return static::$methods[$method]->call($this, ...$args);
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
