<?php

namespace Kirby\Cache;

use Exception;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

/**
 * File System Cache Driver
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class FileCache extends Cache
{

    /**
     * Set all parameters which are needed for the file cache
     * see defaults for available parameters
     *
     * @param array $params
     */
    public function __construct(array $params)
    {
        $defaults = [
            'root'      => null,
            'extension' => null
        ];

        $this->options = array_merge($defaults, $params);

        // try to create the directory
        Dir::make($this->options['root'], true);

        // check for a valid cache directory
        if (is_dir($this->options['root']) === false) {
            throw new Exception('The cache directory does not exist');
        }
    }

    /**
     * Returns the full path to a file for a given key
     *
     * @param  string $key
     * @return string
     */
    protected function file(string $key): string
    {
        $extension = isset($this->options['extension']) ? '.' . $this->options['extension'] : '';
        return $this->options['root'] . '/' . $key . $extension;
    }

    /**
     * Write an item to the cache for a given number of minutes.
     *
     * <code>
     *    // Put an item in the cache for 15 minutes
     *    Cache::set('value', 'my value', 15);
     * </code>
     *
     * @param  string  $key
     * @param  mixed   $value
     * @param  int     $minutes
     */
    public function set(string $key, $value, int $minutes = 0)
    {
        $data = serialize($this->value($value, $minutes));
        return F::write($this->file($key), $data);
    }

    /**
     * Retrieve an item from the cache.
     *
     * @param  string  $key
     * @return mixed
     */
    public function retrieve(string $key)
    {
        return unserialize(F::read($this->file($key)));
    }

    /**
     * Checks when the cache has been created
     *
     * @param string $key
     * @return int
     */
    public function created(string $key): int
    {
        // use the modification timestamp
        // as indicator when the cache has been created/overwritten
        clearstatcache();

        // get the file for this cache key
        $file = $this->file($key);
        return file_exists($file) ? filemtime($this->file($key)) : 0;
    }

    /**
     * Remove an item from the cache
     *
     * @param  string $key
     * @return boolean
     */
    public function remove(string $key): bool
    {
        return F::remove($this->file($key));
    }

    /**
     * Flush the entire cache directory
     *
     * @return boolean
     */
    public function flush(): bool
    {
        if (Dir::remove($this->options['root']) === true && Dir::make($this->options['root']) === true) {
            return true;
        }

        return false;
    }
}
