<?php

namespace Kirby\Util;

use stdClass;

/**
 * An extended version of stdClass objects
 * with a nicer API
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Object extends stdClass
{

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $val) {
            if (!is_string($key) || strlen($key) === 0) {
                continue;
            }
            $this->{$key} = $val;
        }
    }

    /**
     * Magic getter
     *
     * @param  string $method
     * @param  array  $arguments
     * @return mixed
     */
    public function __call(string $method, array $arguments)
    {
        return isset($this->$method) ? $this->$method : null;
    }

    /**
     * Attribute setter
     *
     * @param   string  $key
     * @param   mixed   $value
     * @return  Object
     */
    public function set(string $key, $value): self
    {
        $this->$key = $value;
        return $this;
    }

    /**
     * Attribute getter
     *
     * @param   string  $key
     * @param   mixed   $default (optional)
     * @return  mixed
     */
    public function get(string $key, $default = null)
    {
        return isset($this->$key) ? $this->$key : $default;
    }

    /**
     * Converts the object to an array
     *
     * @return array
     */
    public function toArray(): array
    {
        return (array)$this;
    }

    /**
     * Improved var_dump() output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }
}
