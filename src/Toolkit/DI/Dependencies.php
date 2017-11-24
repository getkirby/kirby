<?php

namespace Kirby\Toolkit\DI;

use Closure;
use Exception;
use ReflectionFunction;
use ReflectionMethod;

/**
 * Kirby's Dependency Container
 * helps organizing and configuring
 * dependencies for your classes in
 * a clean and testable way. Dependencies
 * can be simple string values, instances
 * or factories.
 *
 * @package   Kirby Toolkit
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Dependencies
{

    /**
     * List of all registered dependencies
     *
     * @var array
     */
    protected $dependencies = [];

    /**
     * List of all initialized singleton objects
     *
     * @var array
     */
    protected $singletons = [];

    /**
     * Registers a new dependency
     *
     * @param  string                $name
     * @param  string|Closure|object $object
     * @param  array                 $options
     * @return Dependencies
     */
    public function set(string $name, $object, array $options = []): self
    {
        // normalize the name
        $name = strtolower($name);

        if (isset($this->dependencies[$name]) && $this->dependencies[$name]['locked'] === true) {
            throw new Exception('"' . $name . '" can no longer be overwritten');
        }

        $this->dependencies[$name] = [
            'object'    => $object,
            'singleton' => $options['singleton'] ?? false,
            'locked'    => false
        ];

        return $this;
    }

    /**
     * Registers a new singleton. This is an alias
     * for the set method with the predefined singleton
     * option set to true.
     *
     * @param  string                $name
     * @param  string|Closure|object $object
     * @return Dependencies
     */
    public function singleton(string $name, $object): self
    {
        return $this->set($name, $object, [
            'singleton' => true
        ]);
    }

    /**
     * Checks if a dependency is registered
     *
     * @param  string  $name
     * @return boolean
     */
    public function has(string $name): bool
    {
        return array_key_exists(strtolower($name), $this->dependencies) === true;
    }

    /**
     * Initializes a dependency class
     *
     * @param  string|Closure|object $dependency
     * @param  array                 $args
     * @return object
     */
    public function initialize($dependency, $args = [])
    {
        if (is_callable($dependency)) {
            $object = $dependency->call($this, ...$args);
        } elseif (is_object($dependency)) {
            $object = $dependency;
        } elseif (class_exists($dependency)) {
            $object = new $dependency(...$args);
        } else {
            $object = $dependency;
        }

        return $object;
    }

    /**
     * Returns a dependency value or instance
     * if the dependency has been registered before
     * Once a dependency has been initialized,
     * it will be locked and can no longer be overwritten.
     *
     * @param  string $name
     * @param  array  $args
     * @return object
     */
    public function get(string $name, ...$args)
    {
        // normalize the name
        $name = strtolower($name);

        if (isset($this->dependencies[$name]) === false) {
            throw new Exception('The dependency does not exist: ' . $name);
        }

        $dependency = $this->dependencies[$name];

        // block further dependency setting
        $this->dependencies[$name]['locked'] = true;

        if ($dependency['singleton'] === true) {
            if (isset($this->singletons[$name])) {
                return $this->singletons[$name];
            }

            return $this->singletons[$name] = $this->initialize($dependency['object'], $args);
        }

        return $this->initialize($dependency['object'], $args);
    }

    /**
     * Injects all dependencies into a method or closure
     * You can pass additional arguments for the method and
     * even bind a new instance.
     *
     * 1. simple
     * ```
     * $deps->set('Foo', '\Foo');
     * $deps->set('Bar', '\Bar');
     * $deps->call(function(Foo $foo, Bar $bar) {
     *     // ...
     * });
     * ```
     *
     * 2. with additional arguments
     * ```
     * $deps->set('Foo', '\Foo');
     * $deps->set('Bar', '\Bar');
     * $deps->call(function(Foo $foo, Bar $bar, $arg) {
     *     // ...
     * }, ['arg' => 'test']);
     * ```
     *
     * 3. binding a new instance
     * ```
     * $app = new App;
     * $deps->set('Foo', '\Foo');
     * $deps->set('Bar', '\Bar');
     * $deps->call(function(Foo $foo, Bar $bar, $arg) {
     *     // ...
     * }, ['arg' => 'test'], $app);
     * ```
     *
     * @param  callable $func
     * @param  array    $args
     * @param  object   $bind
     * @return mixed
     */
    public function call(callable $func, array $args = [], $bind = null)
    {

        if (is_array($func) === true) {
            $reflection = new ReflectionMethod(...$func);
        } else {
            $reflection = new ReflectionFunction($func);
        }

        $arguments = [];

        foreach ($reflection->getParameters() as $i => $parameter) {
            $name = $parameter->getName();
            $type = (string)$parameter->getType();

            if ($this->has($type)) {
                $arguments[] = $this->get($type);
            } elseif (isset($args[$name])) {
                $arguments[] = $args[$name];
            } elseif ($this->has($name)) {
                $arguments[] = $this->get($name);
            } elseif (isset($args[$i])) {
                $arguments[] = $args[$i];
            } else {
                if ($parameter->isOptional() === false) {
                    throw new Exception('The parameter "' . $name . '" is missing');
                }

                $arguments[] = null;
            }
        }

        if ($func instanceof Closure && $bind !== null) {
            return $func->call($bind, ...$arguments);
        }

        return call_user_func($func, ...$arguments);
    }
}
