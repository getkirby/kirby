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
     * Calls a registered method class with the
     * passed arguments
     *
     * @param  string $method
     * @param  array  $args
     * @return mixed
     */
    public function callMethod(string $method, array $args = [])
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
