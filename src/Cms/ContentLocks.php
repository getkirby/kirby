<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Toolkit\F;

use Throwable;

/**
 * Manages all content lock files
 *
 * @package   Kirby Cms
 * @author    Nico Hoffmann <nico@getkirby.com>
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
     * Returns the path to a model's lock file
     *
     * @param ModelWithContent $model
     * @return string
     */
    public static function file(ModelWithContent $model): string
    {
        return $model->contentFileDirectory() . '/.lock';
    }

    /**
     * Returns the lock/unlock data for the specified model
     *
     * @param ModelWithContent $model
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

        try {
            $data = Data::read($file, 'yaml');
        } catch (Throwable $th) {
            $data = [];
        }

        $this->data[$file] = $data;

        return $this->data[$file][$id] ?? [];
    }

    /**
     * Returns model ID used as the key for the data array;
     * prepended with a slash because the $site otherwise won't have an ID
     *
     * @param ModelWithContent $model
     * @return string
     */
    public static function id(ModelWithContent $model): string
    {
        return '/' . $model->id();
    }

    /**
     * Sets and writes the lock/unlock data for the specified model
     *
     * @param ModelWithContent $model
     * @param array $data
     * @return boolean
     */
    public function set(ModelWithContent $model, array $data): bool
    {
        $file = static::file($model);
        $id   = static::id($model);

        $this->data[$file][$id] = $data;

        // make sure to unset model id entries,
        // if no lock data for the model exists
        foreach ($this->data[$file] as $id => $data) {
            if (
                isset($data['lock']) === false &&
                (isset($data['unlock']) === false ||
                count($data['unlock']) === 0)
            ) {
                // there is no data for that model whatsoever
                unset($this->data[$file][$id]);
            } elseif (
                isset($data['unlock']) === true &&
                count($data['unlock']) === 0
            ) {
                // there is empty unlock data, but still lock data
                unset($this->data[$file][$id]['unlock']);
            }
        }

        // there is no data left in the file whatsoever, delete the file
        if (count($this->data[$file]) === 0) {
            return F::remove($file);
        }

        return Data::write($file, $this->data[$file], 'yaml');
    }
}
