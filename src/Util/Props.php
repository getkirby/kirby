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
     * Array of frozen properties
     *
     * @var array
     */
    protected $frozen = [];

    /**
     * All stored properties
     *
     * @var array
     */
    protected $props = [];

    /**
     * Array of props that need to
     * be resolved on the next get
     *
     * @var array
     */
    protected $resolve = [];

    /**
     * The schema definition
     *
     * @var array
     */
    protected $schema = [];

    /**
     * Array of used properties
     *
     * @var array
     */
    protected $used = [];

    /**
     * Magic prop caller
     *
     * ```
     * $props->id();
     * ```
     *
     * @param string $key
     * @param mixed $args
     * @return mixed
     */
    public function __call(string $key, $args)
    {
        return empty($args) === true ? $this->get($key) : $this->set($key, ...$args);
    }

    /**
     * Creates a new props container
     * with schema definition
     *
     * @param array|Schema $schema
     * @param array $props Set to null to stop the initial setter on construction
     * @param object $bind
     */
    public function __construct($schema, array $props = [], $bind = null)
    {
        $this->bind   = $bind ?? $this;
        $this->schema = is_a($schema, Schema::class) ? $schema : new Schema($schema);

        $this->setup($props);
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
     * Set the object contect for the resolvers
     *
     * @param object $bind
     * @return self
     */
    public function bind($bind): self
    {
        $this->bind = $bind;
        return $this;
    }

    /**
     * Returns a cloned version of the props container
     *
     * @return self
     */
    public function clone()
    {
        return clone $this;
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
        return $this->resolve($key, 'default');
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
     * Returns a prop by its name.
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        // store prop usage to enable
        // prop freezing on sequential setters
        $this->used[] = $key;

        // get the prop value
        $value = $this->props[$key] ?? null;

        // check for prop values that need resolving
        if (isset($this->resolve[$key]) === true) {
            $value = $this->resolve($key, $this->resolve[$key]);

            // does not need resolving the next time
            unset($this->resolve[$key]);

            $this->schema->validate($key, $value);
            $this->props[$key] = $value;
        }

        return $value;
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
     * Set multiple props at once
     *
     * @param array $values
     * @return self
     */
    public function import(array $values): self
    {
        // make sure to set all keys. also those who are only in the schema,
        // but have not been passed by the input array
        $keys = array_unique(array_merge(array_keys($values), $this->schema->keys()));

        foreach ($keys as $key) {
            $this->set($key, $values[$key] ?? null);
        }

        return $this;
    }

    /**
     * Checks if the property is frozen.
     * Frozen properties can only be set as
     * long as they have not been used.
     *
     * @param string $key
     * @return boolean
     */
    public function isFrozen(string $key): bool
    {
        if (($this->schema->get($key)['freeze'] ?? false) === false) {
            return false;
        }

        if (in_array($key, $this->used) !== true) {
            return false;
        }

        return true;
    }

    /**
     * Returns a list of all prop keys
     *
     * @return array
     */
    public function keys(bool $strict = false): array
    {
        return array_keys($this->toArray($strict));
    }

    /**
     * Removes props from the schema
     * and also unsets them from the props list
     *
     * @return self
     */
    public function not(...$not)
    {
        $clone = $this->clone();
        $clone->schema->remove(...$not);

        foreach ($not as $key) {
            if (is_array($key) === true) {
                $clone = $clone->not(...$key);
            } else {
                unset($clone->props[$key]);
            }
        }

        return $clone;
    }

    /**
     * Resolve values in the schema, which might
     * be setup as Closures.
     *
     * @param string $key
     * @param string $field
     * @return mixed
     */
    protected function resolve(string $key, string $field)
    {
        if ($schema = $this->schema->get($key)) {
            $value = $schema[$field] ?? null;
            if (is_a($value, Closure::class) === true) {
                $value = $value->call($this->bind);
            }
            return $value;
        }

        return null;
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
    public function set($key, $value = null): self
    {
        // batch import
        if (is_array($key) === true) {
            return $this->import($key);
        }

        // get the schema definition for this key
        $schema = $this->schema->get($key);

        // check for props with predefined values
        if (isset($schema['value']) === true) {
            throw new Exception(sprintf('The fixed value for the "%s" property cannot be overwritten', $key));
        }

        // check for frozen props
        if (isset($schema['freeze']) === true && $this->isFrozen($key) === true) {
            throw new Exception(sprintf('The "%s" property has already been used and cannot be overwritten', $key));
        }

        // make sure to no longer resolve this value
        unset($this->resolve[$key]);

        // validate the final prop value
        $this->schema->validate($key, $value);

        // set the prop
        $this->props[$key] = $value;

        // return the updated Props object
        return $this;
    }

    /**
     * This is run once on construction to setup
     * all prop keys, run initial validation and
     * also set the props that need to be resolved
     * This takes a lot of logic out of the set method
     *
     * @return void
     */
    protected function setup(array $props = [])
    {
        // get the schema definition for all keys
        $schema = $this->schema->toArray();

        // make sure to set all keys. also those who are only in the schema,
        // but have not been passed by the input array
        $keys = array_unique(array_merge(array_keys($props), array_keys($schema)));

        // prepare all props
        $result = [];

        foreach ($keys as $key) {

            $definition = $schema[$key] ?? null;
            $value      = $props[$key]  ?? null;

            if ($value === null && isset($definition['default']) === true) {
                $this->resolve[$key] = 'default';
                continue;
            }

            if (isset($definition['value']) === true) {
                $this->resolve[$key] = 'value';
                continue;
            }

            $this->schema->validate($key, $value);
            $this->props[$key] = $value;

        }

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
