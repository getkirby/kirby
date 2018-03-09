<?php

namespace Kirby\Cms;

use Kirby\Cache\ApcCache;
use Kirby\Cache\Cache;
use Kirby\Cache\FileCache;
use Kirby\Cache\MemCached;

trait AppCaches
{

    protected $caches = [];

    public function cache(string $key)
    {
        if (isset($this->caches[$key]) === true) {
            return $this->caches[$key];
        }

        // get the options for this cache type
        $options = $this->option('cache.' . $key, false);

        if ($options === false) {
            return $this->caches[$key] = new Cache;
        }

        $defaults = [
            'type' => 'file',
            'root' => $this->root('cache') . '/' . str_replace('.', '/', $key)
        ];

        if ($options === true) {
            $options = $defaults;
        } else {
            $options = array_merge($defaults, $options);
        }

        // TODO: make this configurable
        $type  = strtolower($options['type'] ?? null);
        $types = [
            'apc'       => ApcCache::class,
            'file'      => FileCache::class,
            'memcached' => MemCached::class,
        ];

        if (array_key_exists($type, $types) === false) {
            throw new Exception(sprintf('Invalid cache type "%s"', $type));
        }

        $className = $types[$type];

        // initialize the cache class
        return $this->caches[$key] = new $className($options);
    }

}
