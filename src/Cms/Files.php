<?php

namespace Kirby\Cms;

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

    protected function finder()
    {
        if (is_a($this->parent, Page::class) === true) {
            return new FilesFinder($this, $this->parent->id());
        }

        return new FilesFinder($this, null);
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
