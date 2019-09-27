<?php

namespace Kirby\Cache;

use Kirby\Toolkit\Dir;
use Kirby\Toolkit\F;

/**
 * File System Cache Driver
 *
 * @package   Kirby Cache
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://opensource.org/licenses/MIT
 */
class FileCache extends Cache
{
    /**
     * Full root including prefix
     * @var string
     */
    protected $root;

    /**
     * Sets all parameters which are needed for the file cache
     *
     * @param array $options 'root' (required)
     *                       'prefix' (default: none)
     *                       'extension' (file extension for cache files, default: none)
     */
    public function __construct(array $options)
    {
        $defaults = [
            'root'      => null,
            'prefix'    => null,
            'extension' => null
        ];

        parent::__construct(array_merge($defaults, $options));

        // build the full root including prefix
        $this->root = $this->options['root'];
        if (empty($this->options['prefix']) === false) {
            $this->root .= '/' . $this->options['prefix'];
        }

        // try to create the directory
        Dir::make($this->root, true);
    }

    /**
     * Returns the full path to a file for a given key
     *
     * @param string $key
     * @return string
     */
    protected function file(string $key): string
    {
        $file = $this->root . '/' . $key;

        if (isset($this->options['extension'])) {
            return $file . '.' . $this->options['extension'];
        } else {
            return $file;
        }
    }

    /**
     * Writes an item to the cache for a given number of minutes and
     * returns whether the operation was successful
     *
     * <code>
     *   // put an item in the cache for 15 minutes
     *   $cache->set('value', 'my value', 15);
     * </code>
     *
     * @param string $key
     * @param mixed $value
     * @param int $minutes
     * @return bool
     */
    public function set(string $key, $value, int $minutes = 0): bool
    {
        $file = $this->file($key);

        return F::write($file, (new Value($value, $minutes))->toJson());
    }

    /**
     * Internal method to retrieve the raw cache value;
     * needs to return a Value object or null if not found
     *
     * @param string $key
     * @return \Kirby\Cache\Value|null
     */
    public function retrieve(string $key)
    {
        $file = $this->file($key);

        return Value::fromJson(F::read($file));
    }

    /**
     * Checks when the cache has been created;
     * returns the creation timestamp on success
     * and false if the item does not exist
     *
     * @param string $key
     * @return mixed
     */
    public function created(string $key)
    {
        // use the modification timestamp
        // as indicator when the cache has been created/overwritten
        clearstatcache();

        // get the file for this cache key
        $file = $this->file($key);
        return file_exists($file) ? filemtime($this->file($key)) : false;
    }

    /**
     * Removes an item from the cache and returns
     * whether the operation was successful
     *
     * @param string $key
     * @return bool
     */
    public function remove(string $key): bool
    {
        $file = $this->file($key);

        if (is_file($file) === true) {
            return F::remove($file);
        } else {
            return false;
        }
    }

    /**
     * Flushes the entire cache and returns
     * whether the operation was successful
     *
     * @return bool
     */
    public function flush(): bool
    {
        if (Dir::remove($this->root) === true && Dir::make($this->root) === true) {
            return true;
        }

        return false; // @codeCoverageIgnore
    }
}
