<?php

namespace Kirby\Cms;

use Closure;
use Exception;

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
class Object
{

    /**
     * Objects are extendable with
     * the Object::plugin() interface
     */
    use HasPlugins;

    /**
     * The registered props schema for the object.
     * The schema must be registered in the constructor.
     *
     * @var array
     */
    protected $schema = [];

    /**
     * All props data after type checks and validation
     *
     * @var array
     */
    protected $props = [];

    /**
     * Creates a new object with the given props and
     * optional additional schema.
     *
     * @param array $props
     * @param array $schema
     */
    public function __construct(array $props = [], array $schema = [])
    {
        $this->schema = $schema;
        $this->set(array_merge($this->defaults(), $props));
    }

    /**
     * Creates an array with all default values to
     * be injected for those props that don't
     * receive a value from the constructor.
     *
     * @return array
     */
    protected function defaults(): array
    {
        return array_map(function ($prop) {
            return $prop['default'] ?? null;
        }, $this->schema);
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

        if ($schema = ($this->schema[$key] ?? null)) {

            // inject the default value
            if ($value === null) {
                $value = $schema['default'] ?? null;
            }

            // check for required props
            if (($schema['required'] ?? false) === true && $value === null) {
                throw new Exception(sprintf('The "%s" prop is missing', $key));
            }

            // type validation
            if ($value !== null && is_a($value, Closure::class) === false) {
                $this->validateProp($key, $value);
            }

        }

        $this->props[$key] = $value;
        return $this;

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
    protected function validateProp(string $key, $value): bool
    {

        $schema = $this->schema[$key] ?? [];
        $type   = $schema['type'] ?? null;
        $error  = 'The "%s" attribute must be of type "%s" not "%s"';

        if ($type === null) {
            return true;
        }

        if ($type === 'number') {
            if (is_numeric($value) !== true) {
                throw new Exception(sprintf($error, $key, $type, gettype($value)));
            }
        } elseif (is_object($value)) {
            if (is_a($value, $type) !== true) {
                throw new Exception(sprintf($error, $key, $type, get_class($value)));
            }
        } elseif ($type !== gettype($value)) {
            throw new Exception(sprintf($error, $key, $type, gettype($value)));
        }

        return true;

    }

    /**
     * Returns the data for all props
     *
     * @return array
     */
    public function props(): array
    {
        return $this->props;
    }

    /**
     * Compares to objects by their id method
     *
     * @param Object $object
     * @return bool
     */
    public function is(Object $object): bool
    {
        return $this->id() === $object->id();
    }

    /**
     * Checks for an existing prop by the
     * prop name
     *
     * @param string $key
     * @return bool
     */
    protected function hasProp(string $key): bool
    {
        return isset($this->schema[$key]) === true || isset($this->props[$key]) === true;
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
    protected function prop(string $key, array $arguments = [])
    {
        if (isset($this->props[$key]) === false) {
            return null;
        }

        if (is_a($this->props[$key], Closure::class)) {
            $value = $this->props[$key] = $this->props[$key]->call($this, ...$arguments);
            $this->validateProp($key, $value);
            return $this->props[$key] = $value;
        }

        return $this->props[$key];
    }

    /**
     * The magic caller enables access to all
     * registered object plugins as well as to
     * all registered props. Overwrite this
     * method to limit or extend access to those
     * parts of the object.
     *
     * @param string $method
     * @param mixed $arguments
     * @return void
     */
    public function __call(string $method, array $arguments = [])
    {
        if ($this->hasProp($method)) {
            return $this->prop($method, $arguments);
        }

        if ($this->hasPlugin($method)) {
            return $this->plugin($method, $arguments);
        }

        return null;
    }

    /**
     * Converts the object to an array
     * by fetching all registered props.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [];

        foreach ($this->schema as $key => $options) {
            $array[$key] = $this->prop($key);
        }

        return $array;
    }

}
