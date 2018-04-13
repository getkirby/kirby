<?php

namespace Kirby\Cms;

use Closure;

/**
 * The Hooks registry is implanted in the App
 * class and used to create and trigger hooks
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 */
class Hooks
{

    /**
     * @var App
     */
    protected $bind;

    /**
     * @var array
     */
    protected $hooks = [];

    /**
     * Creates a new Hooks registry
     *
     * @param App $bind
     */
    public function __construct($bind)
    {
        $this->bind = $bind;
    }

    /**
     * Registers a new hook
     *
     * @param string $name
     * @param Closure $function
     * @return self
     */
    public function register(string $name, Closure $function)
    {
        if (isset($this->hooks[$name]) === false) {
            $this->hooks[$name] = [];
        }

        $this->hooks[$name][] = $function;
        return $this;
    }

    /**
     * Sets multiple hooks at once
     *
     * @param array $hooks
     * @return self
     */
    public function registerAll(array $hooks): self
    {
        foreach ($hooks as $name => $callbacks) {
            if (is_a($callbacks, Closure::class) === true) {
                $this->register($name, $callbacks);
            } else {
                foreach ($callbacks as $callback) {
                    $this->register($name, $callback);
                }
            }
        }

        return $this;
    }

    /**
     * Triggers all registered hooks for a given
     * name if they exist
     *
     * @param string $name
     * @param mixed ...$arguments
     * @return void
     */
    public function trigger(string $name, ...$arguments)
    {
        if (isset($this->hooks[$name]) === false) {
            return false;
        }

        foreach ((array)$this->hooks[$name] as $function) {
            $function->call($this->bind, ...$arguments);
        }
    }
}
