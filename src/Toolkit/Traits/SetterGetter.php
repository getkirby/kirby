<?php

namespace Kirby\Toolkit\Traits;

/**
 * The SetterGetter Trait enables magic
 * getters and setters for object attributes
 *
 * The base `set()` and `get()` methods have to
 * be implemented by the user class in order to
 * make this trait work correctly. Afterwards
 * the setters and getters can be used like this:
 *
 * `$object->set('foo', 'bar')` equals `$object->foo('bar')`
 * `$object->get('foo')` equals `$object->foo()`
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
trait SetterGetter
{

    /**
     * The main set method has to be implemented
     * by the trait user class
     *
     * @param string $key   The name of the setter
     * @param mixed  $value The given value
     */
    abstract public function set(string $key, $value);

    /**
     * The main get method has to be implemented
     * by the trait user class
     *
     * @param  string $key The name of the getter
     * @return mixed       The found value
     */
    abstract public function get(string $key);

    /**
     * Magic call method to enable named
     * getters and setters
     *
     * @param  string $key  The name of the called method
     * @param  array  $args An optional array of arguments
     * @return mixed        Whatever get and set return
     */
    public function __call(string $key, array $args = [])
    {
        if ($value = $args[0] ?? null) {
            return $this->set($key, $value);
        }

        return $this->get($key);
    }
}
