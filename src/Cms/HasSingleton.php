<?php

namespace Kirby\Cms;

trait HasSingleton
{

    protected static $instance;

    static public function instance(self $instance = null)
    {
        if ($instance === null) {
            return static::$instance ?? new static;
        }

        return static::$instance = $instance;
    }

    static public function destroy()
    {
        static::$instance = null;
    }

}
