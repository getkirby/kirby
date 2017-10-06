<?php

namespace Kirby\App;

use Closure;

class App
{

    protected static $instance;

    protected $attributes = [
        'root' => [],
        'url'  => []
    ];

    public function __construct(array $attributes)
    {
        $this->attributes = array_merge_recursive($this->attributes, $attributes);
        static::$instance = $this;
    }

    public static function instance()
    {
        return static::$instance;
    }

    public function set(string $attribute, $value)
    {
        $this->attributes[$attribute] = $value;
        return $this;
    }

    public function url(string $url = '/')
    {
        return $this->attributes['url'][$url];
    }

    public function root(string $root = '/')
    {
        return $this->attributes['root'][$root];
    }

    public function __call($method, $arguments)
    {
        if (isset($this->attributes[$method]) === true) {

            // resolve callbacks
            if (is_a($this->attributes[$method], Closure::class)) {
                $this->attributes[$method] = $this->attributes[$method]->call($this, $arguments);
            }

            return $this->attributes[$method];
        } else {
            return null;
        }
    }

}
