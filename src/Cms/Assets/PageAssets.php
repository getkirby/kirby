<?php

namespace Kirby\Cms\Assets;

use Kirby\Cms\Assets;
use Kirby\Cms\Page;
use Kirby\FileSystem\Folder;

class PageAssets extends Assets
{

    protected $root;
    protected $page;

    public function __construct(string $root, Page $page)
    {
        $this->root = $root;
        $this->page = $page;
    }

    public function folder()
    {
        return $this->root . '/' . $this->page->id();
    }

    public function create($filename = null): bool
    {

        if ($filename === null) {
            foreach ($this->page->files() as $file) {
                $this->create($file->filename());
            }
            return true;
        }

        if ($file = $this->page->file($filename)) {
            return $this->link($file->realpath(), $this->folder() . '/' . $filename);
        }

        return false;
    }

    public function delete($filename = null): bool
    {
        if ($filename === null) {
            $folder = new Folder($this->folder());
            $folder->delete();

            return true;
        }

        return unlink($this->folder() . '/' . $filename);
    }

}
