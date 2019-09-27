<?php

namespace Kirby\Cms;

/**
 * HasMethods
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
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
     * @internal
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function callMethod(string $method, array $args = [])
    {
        return static::$methods[$method]->call($this, ...$args);
    }

    /**
     * Checks if the object has a registered method
     *
     * @internal
     * @param string $method
     * @return bool
     */
    public function hasMethod(string $method): bool
    {
        return isset(static::$methods[$method]) === true;
    }
}
