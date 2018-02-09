<?php

namespace Kirby\Util;

/**
 * Base object class with full support
 * for our Props and Schema classes to
 * introduce simple prop validation and defaults
 *
 * @package   Kirby Util
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Object
{

    /**
     * All registered props
     *
     * @var Props
     */
    protected $props;

    /**
     * Magic caller to enable prop getter and setter methods
     *
     * ```
     * $object->id();
     * $object->id('some-id');
     * ```
     *
     * @param string $key
     * @param array $args
     * @return void
     */
    public function __call(string $key, array $args = [])
    {
        if (empty($args) === true) {
            return $this->props->get($key);
        }

        return $this->props->set($key, ...$args);
    }

    /**
     * Creates a new Object
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        $this->props = new Props([], $props);
    }

    /**
     * Simplified var_dump output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    /**
     * Magic prop getter
     *
     * ```
     * $object->id;
     * ```
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->props->get($key);
    }

    /**
     * Checks if a prop exists
     *
     * ```
     * isset($object->id);
     * ```
     *
     * @param string $key
     * @return boolean
     */
    public function __isset(string $key)
    {
        return $this->props->has($key);
    }

    /**
     * Magic prop setter
     *
     * ```
     * $object->id = 'some-id';
     * ```
     *
     * @param string $key
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->props->set($key, $value);
    }

    /**
     * Returns all object props
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->props->toArray();
    }

}
