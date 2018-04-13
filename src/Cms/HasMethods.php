<?php

namespace Kirby\Cms;

trait HasMethods
{

    /**
     * All registered methods
     *
     * @var array
     */
    public static $methods = [];

    /**
     * Magic caller
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call(string $method, array $arguments = [])
    {
        // methods
        if ($this->hasMethod($method)) {
            return $this->call($method, $arguments);
        }

        // return an unmodified object otherwise
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
        return static::$methods[$method]->call($this, ...$args);
    }

    /**
     * Checks if the object has a registered method
     *
     * @param string $method
     * @return boolean
     */
    public function hasMethod(string $method): bool
    {
        return isset(static::$methods[$method]) === true;
    }
}
