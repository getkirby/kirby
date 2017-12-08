<?php

namespace Kirby\Cms;

use Closure;
use Kirby\Util\Controller;
use Kirby\FileSystem\Folder;

class Collections extends Object
{

    protected $cache = [];

    public function get($name, array $data = [])
    {
        if (isset($this->cache[$name]) === true) {
            return $this->cache[$name];
        }

        $controller = new Controller($this->props[$name]);

        return $this->cache[$name] = $controller->call(null, $data);
    }

    public function __call(string $name, array $arguments = [])
    {
        return $this->get($name, ...$arguments);
    }

    public static function load($root)
    {
        $collections = [];
        $folder      = new Folder($root);

        foreach ($folder->files() as $file) {

            $collection = require $file;

            if (is_a($collection, Closure::class)) {
                $name = pathinfo($file, PATHINFO_FILENAME);
                $collections[$name] = $collection;
            }
        }

        return new static($collections);
    }

}
