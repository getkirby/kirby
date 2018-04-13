<?php

namespace Kirby\Cms;

use Kirby\Cache\ApcuCache;
use Kirby\Cache\Cache;
use Kirby\Cache\FileCache;
use Kirby\Cache\MemCached;

use Kirby\Exception\InvalidArgumentException;

trait AppCaches
{
    protected $caches = [];

    public function cache(string $key)
    {
        if (isset($this->caches[$key]) === true) {
            return $this->caches[$key];
        }

        // get the options for this cache type
        $options = $this->cacheOptions($key);

        if ($options['active'] === false) {
            return $this->caches[$key] = new Cache;
        }

        // TODO: make this configurable
        $type  = strtolower($options['type']);
        $types = [
            'apcu'      => ApcuCache::class,
            'file'      => FileCache::class,
            'memcached' => MemCached::class,
        ];

        if (array_key_exists($type, $types) === false) {
            throw new InvalidArgumentException([
                'key'  => 'app.invalid.cacheType',
                'data' => ['type' => $type]
            ]);
        }

        $className = $types[$type];

        // initialize the cache class
        return $this->caches[$key] = new $className($options);
    }

    protected function cacheOptions(string $key): array
    {
        $options = $this->option($cacheKey = $this->cacheOptionsKey($key), false);

        if ($options === false) {
            return [
                'active' => false
            ];
        }

        $defaults = [
            'active' => true,
            'type'   => 'file',
            'root'   => $this->root('cache') . '/' . str_replace('.', '/', $key)
        ];

        if ($options === true) {
            return $defaults;
        } else {
            return array_merge($defaults, $options);
        }
    }

    /**
     * Takes care of converting prefixed plugin cache setups
     * to the right cache key, while leaving regular cache
     * setups untouched.
     *
     * @param string $key
     * @return string
     */
    protected function cacheOptionsKey(string $key): string
    {
        $prefixedKey = 'cache.' . $key;

        if (isset($this->options[$prefixedKey])) {
            return $prefixedKey;
        }

        // plain keys without dots don't need further investigation
        // since they can never be from a plugin.
        if (strpos($key, '.') === false) {
            return $prefixedKey;
        }

        // try to extract the plugin name
        $parts        = explode('.', $key);
        $pluginName   = implode('/', array_slice($parts, 0, 2));
        $pluginPrefix = implode('.', array_slice($parts, 0, 2));
        $cacheName    = implode('.', array_slice($parts, 2));

        // check if such a plugin exists
        if ($plugin = $this->plugin($pluginName)) {
            return empty($cacheName) === true ? $pluginPrefix . '.cache' : $pluginPrefix . '.cache.' . $cacheName;
        }

        return $prefixedKey;
    }
}
