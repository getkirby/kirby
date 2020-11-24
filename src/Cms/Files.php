<?php

namespace Kirby\Cms;

/**
 * The `$files` object extends the general
 * `Collection` class and refers to a
 * collection of files, i.e. images, documents
 * etc. Files can be filtered, searched,
 * converted, modified or evaluated with the
 * following methods:
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Files extends Collection
{
    /**
     * All registered files methods
     *
     * @var array
     */
    public static $methods = [];

    /**
     * Adds a single file or
     * an entire second collection to the
     * current collection
     *
     * @param mixed $object
     * @return self
     */
    public function add($object)
    {
        // add a page collection
        if (is_a($object, static::class) === true) {
            $this->data = array_merge($this->data, $object->data);

        // add a file by id
        } elseif (is_string($object) === true && $file = App::instance()->file($object)) {
            $this->__set($file->id(), $file);

        // add a file object
        } elseif (is_a($object, 'Kirby\Cms\File') === true) {
            $this->__set($object->id(), $object);
        }

        return $this;
    }

    /**
     * Sort all given files by the
     * order in the array
     *
     * @param array $files List of file ids
     * @param int $offset Sorting offset
     * @return self
     */
    public function changeSort(array $files, int $offset = 0)
    {
        foreach ($files as $filename) {
            if ($file = $this->get($filename)) {
                $offset++;
                $file->changeSort($offset);
            }
        }

        return $this;
    }

    /**
     * Creates a files collection from an array of props
     *
     * @param array $files
     * @param \Kirby\Cms\Model $parent
     * @return self
     */
    public static function factory(array $files, Model $parent)
    {
        $collection = new static([], $parent);
        $kirby      = $parent->kirby();

        foreach ($files as $props) {
            $props['collection'] = $collection;
            $props['kirby']      = $kirby;
            $props['parent']     = $parent;

            $file = File::factory($props);

            $collection->data[$file->id()] = $file;
        }

        return $collection;
    }

    /**
     * Tries to find a file by id/filename
     *
     * @param string $id
     * @return \Kirby\Cms\File|null
     */
    public function findById(string $id)
    {
        return $this->get(ltrim($this->parent->id() . '/' . $id, '/'));
    }

    /**
     * Alias for FilesFinder::findById() which is
     * used internally in the Files collection to
     * map the get method correctly.
     *
     * @param string $key
     * @return \Kirby\Cms\File|null
     */
    public function findByKey(string $key)
    {
        return $this->findById($key);
    }

    /**
     * Filter all files by the given template
     *
     * @param null|string|array $template
     * @return self
     */
    public function template($template)
    {
        if (empty($template) === true) {
            return $this;
        }

        if ($template === 'default') {
            $template = ['default', ''];
        }

        return $this->filter(
            'template',
            is_array($template) ? 'in' : '==',
            $template
        );
    }
}
