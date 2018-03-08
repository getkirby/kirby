<?php

namespace Kirby\Cms;

trait AppHooks
{

    public function trigger(string $name, ...$arguments)
    {
        if ($functions = static::extension('hooks', $name)) {
            foreach ($functions as $function) {
                $function->call($this, ...$arguments);
            }
        }
    }

}
