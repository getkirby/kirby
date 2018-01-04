<?php

namespace Kirby\Util;

use Closure;
use Exception;

/**
 * The Props container helps to define
 * a validated set of properties that can
 * be reliably modified without injecting
 * unexpected values.
 *
 * @package   Kirby Util
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Props
{

    /**
     * Bind any object with this property
     * to modify the $this context in default
     * value closures
     *
     * @var object
     */
    protected $bind = null;

    /**
     * The schema definition
     *
     * @var array
     */
    protected $schema = [];

    /**
     * All stored properties
     *
     * @var array
     */
    protected $props = [];

    /**
     * Creates a new props container
     * with schema definition
     *
     * @param array|Schema $schema
     * @param array $props
     * @param object $bind
     */
    public function __construct($schema, array $props = [], $bind = null)
    {
        $this->bind   = $bind ?? $this;
        $this->schema = is_a($schema, Schema::class) ? $schema : new Schema($schema);
        $this->set($props);
    }

    /**
     * Improved var_dump output
     *
     * @return array
     */
    public function __debuginfo(): array
    {
        return $this->toArray();
    }

    /**
     * Get a specific prop by key
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->get($key);
    }

    /**
     * Checks if a prop exists
     *
     * @param string $key
     * @return boolean
     */
    public function __isset(string $key)
    {
        return $this->has($key);
    }

    /**
     * Simple prop setter
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function __set(string $key, $value)
    {
        return $this->set($key, $value);
    }

    /**
     * Returns the default value for a specific prop
     * Default values are resolved here, if they are
     * defined as a Closure.
     *
     * @param string $key
     * @return mixed
     */
    public function default(string $key)
    {
        if ($schema = $this->schema->get($key)) {
            $default = $schema['default'] ?? null;
            if (is_a($default, Closure::class)) {
                $default = $default->call($this->bind);
            }
            return $default;
        }

        return null;
    }

    /**
     * Creates an array with all default values to
     * be injected for those props that don't
     * receive a value from the constructor.
     *
     * @return array
     */
    public function defaults(): array
    {
        $defaults = [];

        foreach ($this->schema->toArray() as $key => $definition) {
            $defaults[$key] = $this->default($key);
        }

        return $defaults;
    }

    /**
     * Returns a prop by its name. Additional
     * arguments can be passed to the prop receiver.
     * Those will be passed on to the prop default callback
     *
     * @param string $key
     * @param array $arguments
     * @return mixed
     */
    public function get(string $key = null, array $arguments = [])
    {
        if ($key === null) {
            return $this->toArray();
        }

        return $this->props[$key] ?? $this->default($key);
    }

    /**
     * Checks for an existing prop by the
     * prop name
     *
     * @param string $key
     * @return bool
     */
    public function has(string $key, bool $strict = false): bool
    {
        if ($strict === true) {
            return $this->schema->has($key);
        }

        return $this->schema->has($key) === true || isset($this->props[$key]) === true;
    }

    /**
     * Returns the underlying schema object
     *
     * @return Schema
     */
    public function schema()
    {
        return $this->schema;
    }

    /**
     * Setter for individual pros.
     * The setter can also receive an array
     * as first argument to set multiple
     * props at once.
     *
     * @param string|array $key
     * @param mixed $value
     * @return Object
     */
    public function set($key, $value = null)
    {
        if (is_array($key) === true) {
            foreach ($key as $k => $v) {
                $this->set($k, $v);
            }
            return $this;
        }

        if (empty($key) === true) {
            throw new Exception(sprintf('Invalid property key: "%s"', $key));
        }

        if ($value === null) {
            $value = $this->default($key);
        }

        $this->schema->validate($key, $value);
        $this->props[$key] = $value;
        return $this;
    }

    /**
     * Converts all props to an associative array
     *
     * @param boolean $strict Only return props defined in the schema
     * @return array
     */
    public function toArray($strict = false): array
    {
        if ($strict === true) {
            $keys = $this->schema->keys();
        } else {
            $keys = array_unique(array_merge(array_keys($this->props), $this->schema->keys()));
        }

        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key);
        }

        ksort($result);

        return $result;
    }

    /**
     * Validates a given prop by the rules
     * set in the schema. It validates by
     * the given type and an optional custom
     * validate rule.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function validate($key, $value = null): bool
    {
        return $this->schema->validate($key, $value);
    }

}
