<?php

namespace Kirby\Cms;

/**
 * An extended version of the Collection
 * class, that has custom find methods and
 * a Files::factory method to convert an array
 * into a Files collection.
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
     * Sort all given files by the
     * order in the array
     *
     * @param array $files
     * @return self
     */
    public function changeSort(array $files)
    {
        $index = 0;

        foreach ($files as $filename) {
            if ($file = $this->get($filename)) {
                $index++;
                $file->changeSort($index);
            }
        }

        return $this;
    }

    /**
     * Creates a files collection from an array of props
     *
     * @param array $files
     * @param Model $parent
     * @param array $inject
     * @return Files
     */
    public static function factory(array $files, Model $parent)
    {
        $collection = new static([], $parent);
        $kirby      = $parent->kirby();

        foreach ($files as $props) {
            $props['collection'] = $collection;
            $props['kirby']      = $kirby;
            $props['parent']     = $parent;

            $file = new File($props);

            $collection->data[$file->id()] = $file;
        }

        return $collection;
    }

    /**
     * Tries to find a file by id/filename
     *
     * @param string $id
     * @return File|null
     */
    public function findById($id)
    {
        return $this->get(ltrim($this->parent->id() . '/' . $id, '/'));
    }

    /**
     * Alias for FilesFinder::findById() which is
     * used internally in the Files collection to
     * map the get method correctly.
     *
     * @param string $key
     * @return File|null
     */
    public function findByKey($key)
    {
        return $this->findById($key);
    }

    /**
     * Filter all files by the given template
     *
     * @param null|string|array $template
     * @return self
     */
    public function template($template): self
    {
        if (empty($template) === true) {
            return $this;
        }

        return $this->filterBy('template', is_array($template) ? 'in' : '==', $template);
    }
}
