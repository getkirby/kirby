<?php

namespace Kirby\Toolkit;

/**
 * The Silo class is a core class to handle
 * setting, getting and removing static data of
 * a singleton.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Silo
{
    /**
     * @var array
     */
    public static $data = [];

    /**
     * Setter for new data.
     *
     * @param string|array $key
     * @param mixed $value
     * @return array
     */
    public static function set($key, $value = null): array
    {
        if (is_array($key) === true) {
            return static::$data = array_merge(static::$data, $key);
        } else {
            static::$data[$key] = $value;
            return static::$data;
        }
    }

    /**
     * @param string|array $key
     * @param mixed $default
     * @return mixed
     */
    public static function get($key = null, $default = null)
    {
        if ($key === null) {
            return static::$data;
        }

        return A::get(static::$data, $key, $default);
    }

    /**
     * Removes an item from the data array
     *
     * @param string|null $key
     * @return array
     */
    public static function remove(string $key = null): array
    {
        // reset the entire array
        if ($key === null) {
            return static::$data = [];
        }

        // unset a single key
        unset(static::$data[$key]);

        // return the array without the removed key
        return static::$data;
    }
}
