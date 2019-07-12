<?php

namespace Kirby\Cms;

use Kirby\Cache\Cache;
use Kirby\Cache\NullCache;
use Kirby\Exception\InvalidArgumentException;

/**
 * AppCaches
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
trait AppCaches
{
    protected $caches = [];

    /**
     * Returns a cache instance by key
     *
     * @param string $key
     * @return Kirby\Cache\Cache
     */
    public function cache(string $key)
    {
        if (isset($this->caches[$key]) === true) {
            return $this->caches[$key];
        }

        // get the options for this cache type
        $options = $this->cacheOptions($key);

        if ($options['active'] === false) {
            // use a dummy cache that does nothing
            return $this->caches[$key] = new NullCache;
        }

        $type  = strtolower($options['type']);
        $types = $this->extensions['cacheTypes'] ?? [];

        if (array_key_exists($type, $types) === false) {
            throw new InvalidArgumentException([
                'key'  => 'app.invalid.cacheType',
                'data' => ['type' => $type]
            ]);
        }

        $className = $types[$type];

        // initialize the cache class
        $cache = new $className($options);

        // check if it is a useable cache object
        if (is_a($cache, Cache::class) !== true) {
            throw new InvalidArgumentException([
                'key'  => 'app.invalid.cacheType',
                'data' => ['type' => $type]
            ]);
        }

        return $this->caches[$key] = $cache;
    }

    /**
     * Returns the cache options by key
     *
     * @param string $key
     * @return array
     */
    protected function cacheOptions(string $key): array
    {
        $options = $this->option($cacheKey = $this->cacheOptionsKey($key), false);

        if ($options === false) {
            return [
                'active' => false
            ];
        }

        $prefix = str_replace(['/', ':'], '_', $this->system()->indexUrl()) .
                  '/' .
                  str_replace('.', '/', $key);

        $defaults = [
            'active'    => true,
            'type'      => 'file',
            'extension' => 'cache',
            'root'      => $this->root('cache'),
            'prefix'    => $prefix
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
        if ($this->plugin($pluginName)) {
            return empty($cacheName) === true ? $pluginPrefix . '.cache' : $pluginPrefix . '.cache.' . $cacheName;
        }

        return $prefixedKey;
    }
}
