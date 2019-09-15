<?php

namespace Kirby\Cms;

use Kirby\Data\Yaml;
use Kirby\Exception\Exception;
use Kirby\Toolkit\F;

/**
 * Manages all content lock files
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>,
 *            Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class ContentLocks
{
    /**
     * Data from the `.lock` files
     * that have been read so far
     * cached by `.lock` file path
     *
     * @var array
     */
    protected $data = [];

    /**
     * PHP file handles for all currently
     * open `.lock` files
     *
     * @var array
     */
    protected $handles = [];

    /**
     * Closes the open file handles
     *
     * @codeCoverageIgnore
     */
    public function __destruct()
    {
        foreach ($this->handles as $file => $handle) {
            $this->closeHandle($file);
        }
    }

    /**
     * Removes the file lock and closes the file handle
     *
     * @param string $file
     * @return void
     */
    protected function closeHandle(string $file)
    {
        if (isset($this->handles[$file]) === false) {
            return;
        }

        $handle = $this->handles[$file];
        $result = flock($handle, LOCK_UN) && fclose($handle);

        if ($result !== true) {
            throw new Exception('Unexpected file system error.'); // @codeCoverageIgnore
        }

        unset($this->handles[$file]);
    }

    /**
     * Returns the path to a model's lock file
     *
     * @param \Kirby\Cms\ModelWithContent $model
     * @return string
     */
    public static function file(ModelWithContent $model): string
    {
        return $model->contentFileDirectory() . '/.lock';
    }

    /**
     * Returns the lock/unlock data for the specified model
     *
     * @param \Kirby\Cms\ModelWithContent $model
     * @return array
     */
    public function get(ModelWithContent $model): array
    {
        $file = static::file($model);
        $id   = static::id($model);

        // return from cache if file was already loaded
        if (isset($this->data[$file]) === true) {
            return $this->data[$file][$id] ?? [];
        }

        // first get a handle to ensure a file system lock
        $handle = $this->handle($file);

        if (is_resource($handle) === true) {
            // read data from file
            clearstatcache();
            $filesize = filesize($file);

            if ($filesize > 0) {
                // always read the whole file
                rewind($handle);
                $string = fread($handle, $filesize);
                $data   = Yaml::decode($string);
            }
        }

        $this->data[$file] = $data ?? [];

        return $this->data[$file][$id] ?? [];
    }

    /**
     * Returns the file handle to a `.lock` file
     *
     * @param string $file
     * @param boolean $create Whether to create the file if it does not exist
     * @return resource|null File handle
     */
    protected function handle(string $file, bool $create = false)
    {
        // check for an already open handle
        if (isset($this->handles[$file]) === true) {
            return $this->handles[$file];
        }

        // don't create a file if not requested
        if (is_file($file) !== true && $create !== true) {
            return null;
        }

        $handle = @fopen($file, 'c+b');
        if (is_resource($handle) === false) {
            throw new Exception('Lock file ' . $file . ' could not be opened.'); // @codeCoverageIgnore
        }

        // lock the lock file exclusively to prevent changes by other threads
        $result = flock($handle, LOCK_EX);
        if ($result !== true) {
            throw new Exception('Unexpected file system error.'); // @codeCoverageIgnore
        }

        return $this->handles[$file] = $handle;
    }

    /**
     * Returns model ID used as the key for the data array;
     * prepended with a slash because the $site otherwise won't have an ID
     *
     * @param \Kirby\Cms\ModelWithContent $model
     * @return string
     */
    public static function id(ModelWithContent $model): string
    {
        return '/' . $model->id();
    }

    /**
     * Sets and writes the lock/unlock data for the specified model
     *
     * @param \Kirby\Cms\ModelWithContent $model
     * @param array $data
     * @return boolean
     */
    public function set(ModelWithContent $model, array $data): bool
    {
        $file   = static::file($model);
        $id     = static::id($model);
        $handle = $this->handle($file, true);

        $this->data[$file][$id] = $data;

        // make sure to unset model id entries,
        // if no lock data for the model exists
        foreach ($this->data[$file] as $id => $data) {
            // there is no data for that model whatsoever
            if (
                isset($data['lock']) === false &&
                (isset($data['unlock']) === false ||
                count($data['unlock']) === 0)
            ) {
                unset($this->data[$file][$id]);

            // there is empty unlock data, but still lock data
            } elseif (
                isset($data['unlock']) === true &&
                count($data['unlock']) === 0
            ) {
                unset($this->data[$file][$id]['unlock']);
            }
        }

        // there is no data left in the file whatsoever, delete the file
        if (count($this->data[$file]) === 0) {
            unset($this->data[$file]);

            // close the file handle, otherwise we can't delete it on Windows
            $this->closeHandle($file);

            return F::remove($file);
        }

        $yaml = Yaml::encode($this->data[$file]);

        // delete all file contents first
        if (rewind($handle) !== true || ftruncate($handle, 0) !== true) {
            throw new Exception('Could not write lock file ' . $file . '.'); // @codeCoverageIgnore
        }

        // write the new contents
        $result = fwrite($handle, $yaml);
        if (is_int($result) === false || $result === 0) {
            throw new Exception('Could not write lock file ' . $file . '.'); // @codeCoverageIgnore
        }

        return true;
    }
}
