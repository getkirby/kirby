<?php

namespace Kirby\Toolkit;

/**
 * Laravel-style static facades
 * for class instances
 */
abstract class Facade
{

    /**
     * Returns the instance that should be
     * available statically
     *
     * @return mixed
     */
    abstract public static function instance();

    /**
     * Proxy for all public instance calls
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic(string $method, array $args = null)
    {
        return static::instance()->$method(...$args);
    }
}
