<?php

namespace Kirby\Cms;

use Closure;

class Hooks
{

    protected $bind;
    protected $hooks = [];

    public function __construct($bind)
    {
        $this->bind = $bind;
    }

    public function register($name, Closure $function)
    {
        if (isset($this->hooks[$name]) === false) {
            $this->hooks[$name] = [];
        }

        $this->hooks[$name][] = $function;
        return $this;
    }

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
