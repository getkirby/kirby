<?php

namespace Kirby\Cms;

use Closure;

trait HasEvents
{

    protected static $events = [];

    static public function on(string $event, Closure $callback)
    {
        if (isset(static::$events[$event]) === false) {
            static::$events[$event] = [];
        }

        static::$events[$event][] = $callback;
    }

    public function trigger($event, ...$arguments)
    {
        foreach ((static::$events[$event] ?? []) as $callback) {
            return $callback->call($this, ...$arguments);
        }
    }

}
