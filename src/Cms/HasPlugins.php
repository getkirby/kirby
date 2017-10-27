<?php

namespace Kirby\Cms;

use Closure;

trait HasPlugins
{

    protected static $plugins = [];

    public static function __callStatic($method, $arguments)
    {
        return static::$plugins[$method] ?? null;
    }

    protected function hasPlugin($key)
    {
        return isset(static::$plugins[$key]) === true;
    }

    protected function plugin(string $key, array $arguments = [])
    {
        if ($this->hasPlugin($key) === false) {
            throw new Exception(sprintf('The plugin "%s" does not exist', $key));
        }

        if (is_a(static::$plugins[$key], Closure::class)) {
            return static::$plugins[$key] = static::$plugins[$key]->call($this, ...$arguments);
        }

        return static::$plugins[$key];
    }

    public static function use(string $key, $value)
    {
        static::$plugins[$key] = $value;
    }

}
