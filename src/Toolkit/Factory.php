<?php

namespace Kirby\Toolkit;

use Closure;
use Exception;

/**
 * Simple factory pattern implementation
 *
 * @package   Kirby Util
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Factory
{

    /**
     * All registered factory setups
     * Each factory is registered as
     * a simple array with the singleton
     * option and the instance constructor
     *
     * @var array
     */
    protected $factories = [];

    /**
     * All created instances.
     * This is used to always return
     * the same instance for singletons
     *
     * @var array
     */
    protected $instances = [];

    /**
     * Creates a new factory container
     * All factories have to be set here.
     * Once the constructor ran, no new
     * factories can be registered
     *
     * @param array $factories
     */
    public function __construct($factories = [])
    {
        foreach ($factories as $name => $factory) {
            $this->set($name, $factory);
        }
    }

    /**
     * Returns a factory by name, if it exists
     * You can pass any number of arguments to
     * the instance construction of the factory
     *
     * @param string $name
     * @param mixed ...$arguments
     * @return mixed
     */
    public function get(string $name, ...$arguments)
    {
        $name = strtolower($name);

        if (isset($this->factories[$name]) === false) {
            throw new Exception('The factory does not exist');
        }

        $factory   = $this->factories[$name];
        $instance  = $factory['instance'] ?? null;
        $singleton = $factory['singleton'] ?? false;

        // handle singletons
        if ($singleton === true) {
            if (isset($this->instances[$name]) === true) {
                return $this->instances[$name];
            }
        }

        if (is_a($instance, Closure::class) === false) {
            if (is_object($instance) === true) {
                return $this->instances[$name] = $instance;
            }
            throw new Exception('Invalid factory instance creator');
        }

        $object = $instance(...$arguments);

        // integrity check for class instances
        if (is_a($object, $factory['type'] ?? null) === false) {
            throw new Exception('The factory returns an invalid class: ' . get_class($object) . ' expected: ' . $factory['type']);
        }

        return $this->instances[$name] = $object;
    }

    /**
     * Internal setter for new factories
     * This is protected to not accept new
     * factories after the constructor ran
     *
     * @param string $name The name must be the absolute class name.
     *                     This will be used in the getter to verify
     *                     instances or extensions of the same class
     * @param array|Closure|object $factory
     * @return self
     */
    protected function set(string $name, $factory): self
    {
        $name = strtolower($name);

        if (is_object($factory)) {
            $factory = [
                'singleton' => is_a($factory, Closure::class) === false,
                'type'      => get_class($factory),
                'instance'  => $factory
            ];
        }

        if (is_array($factory) === false || isset($factory['instance']) === false) {
            throw new Exception('Invalid factory setup');
        }

        $this->factories[$name] = $factory;
        return $this;
    }
}
