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
    protected static $accept = File::class;

    /**
     * Creates a files collection from an array of props
     *
     * @param array $files
     * @param Model $parent
     * @param array $inject
     * @return Files
     */
    public static function factory(array $files, Model $parent = null, array $inject = [])
    {
        $collection = new static([], $parent);

        foreach ($files as $props) {
            $file = new File($props + $inject + [
                'collection' => $collection
            ]);

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
     * @param string $template
     * @return self
     */
    public function template(string $template): self
    {
        return $this->filterBy('template', '==', $template);
    }
}
