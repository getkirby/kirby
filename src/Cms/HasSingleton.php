<?php

namespace Kirby\Cms;

trait HasSingleton
{
    protected static $instance;

    public static function instance(self $instance = null)
    {
        if ($instance === null) {
            return static::$instance ?? new static;
        }

        return static::$instance = $instance;
    }

    public static function destroy()
    {
        static::$instance = null;
    }
}
