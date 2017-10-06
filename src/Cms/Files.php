<?php

namespace Kirby\Cms;

use Closure;
use Exception;

use Kirby\Cms\Files\Finder;
use Kirby\Collection\Collection;
use Kirby\FileSystem\Folder;

class Files extends Collection
{

    protected $parent;

    public function __construct($files = [], $parent = null)
    {
        $this->parent = $parent;
        parent::__construct($files);
    }

    protected function finder()
    {
        if (is_a($this->parent, Page::class) === true) {
            return new Finder($this, $this->parent->id());
        }

        return new Finder($this, null);
    }

    public function __set(string $id, $file)
    {

        if (is_array($file) === true) {
            $file = new File($file);
        }

        if (is_a($file, File::class) === false) {
            throw new Exception('Invalid file object in files collection');
        }

        // inject the collection for proper navigation
        $file->collection($this);

        return parent::__set($file->id(), $file);

    }

    public function getAttribute($item, $attribute)
    {
        return (string)$item->$attribute();
    }

}
