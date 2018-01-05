<?php

namespace Kirby\Cms;

use Kirby\Util\Object as BaseObject;
use Kirby\Util\Props;
use Kirby\Util\Schema;

/**
 * The Object class is a base component
 * class that can be used to create objects
 * with typed and validated properties easily.
 *
 * Most Kirby Cms classes are based on this.
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Object extends BaseObject
{

    use HasPlugins;

    /**
     * Magic caller to enable getter methods
     *
     * ```
     * $object->id();
     * ```
     *
     * @param string $key
     * @param array $args
     * @return void
     */
    public function __call(string $key, array $args = [])
    {
        return $this->get($key, $args);
    }

    /**
     * Creates a new object with the given props
     *
     * @param array $props
     */
    public function __construct(array $props = [])
    {
        $this->props = new Props($this->schema(), $props, $this);
    }

    /**
     * Getter for props
     *
     * @param string
     * @return mixed
     */
    public function get(string $key)
    {
        if ($this->props->has($key)) {
            return $this->props->get($key);
        }

        if ($this->hasPlugin($key)) {
            return $this->plugin($key);
        }

        return null;
    }

    /**
     * Compares two objects
     *
     * @param Object $object
     * @return bool
     */
    public function is(Object $object): bool
    {
        return $this->id() === $object->id();
    }

    /**
     * Sets the schema for the props
     *
     * @return array|Schema
     */
    protected function schema()
    {
        return [];
    }

    /**
     * Setter for props
     *
     * @param string|array $key
     * @param mixed $value
     * @return self
     */
    public function set($key, $value = null)
    {
        $this->props->set($key, $value);
        return $this;
    }

    public function toArray(): array
    {
        return $this->props->clone()->not('collection')->toArray(true);
    }

}
