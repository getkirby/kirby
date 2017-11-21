<?php

namespace Kirby\Toolkit\Traits;

use Closure;
use Exception;

/**
 * Custom Methods Traits
 *
 * Adds the option to define any number of custom methods
 * for the Class, using this trait. Methods can be registered with
 * `Class::method('myMethod', function() {})` and afterwards called
 * by the given name. I.e. `(new Class())->myMethod()`
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
trait CustomMethods
{

    /**
     * Store for all custom methods
     *
     * @var array
     */
    protected static $customMethods = [];

    /**
     * Registers a new custom method
     *
     * @param  string  $name The name of the custom method (camelCase prefered)
     * @param  Closure $func The anonymous custom method
     * @return Closure       Returns the defined Closure
     */
    public static function addCustomMethod(string $name, Closure $func): Closure
    {
        return static::$customMethods[$name] = $func;
    }

    /**
     * Uninstall all custom methods for the class
     *
     * @return array
     */
    public static function removeCustomMethods(): array
    {
        return static::$customMethods = [];
    }

    /**
     * Checks if a custom method exists
     *
     * @param  string  $name
     * @return boolean
     */
    public function hasCustomMethod(string $name): bool
    {
        return isset(static::$customMethods[$name]);
    }

    /**
     * Calls a custom method by name
     *
     * @param  string $name The name of the custom method.
     *                      Must match, including upper/lowercase
     * @param  array  $args Optional arguments to pass to the custom method.
     *                      Multiple args have to be passed as an array.
     * @return mixed        The result of the custom method call.
     */
    public function callCustomMethod(string $name, array $args = [])
    {
        if ($this->hasCustomMethod($name)) {
            return static::$customMethods[$name]->call($this, ...$args);
        } else {
            throw new Exception('Invalid custom method: ' . $name);
        }
    }

    /**
     * Magic method to call the custom method
     * natively on the object.
     *
     * @param  string $name The name of the custom method.
     *                      Must match including upper/lowercase
     * @param  array  $args Optional arguments. In native methods,
     *                      a list of arguments can be passed.
     * @return mixed        The result of the custom method call.
     */
    public function __call(string $name, array $args = [])
    {
        return $this->callCustomMethod($name, $args);
    }
}
