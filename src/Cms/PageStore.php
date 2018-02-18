<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Base\Base;
use Kirby\Util\Dir;
use Kirby\Util\F;

class PageStore extends PageStoreDefault
{

    protected $base;
    protected $root;

    public function base()
    {
        if (is_a($this->base, Base::class) === true) {
            return $this->base;
        }

        return $this->base = new Base([
            'extension' => 'txt',
            'root'      => $this->root()
        ]);
    }

    public function changeNum(int $num = null)
    {
        if ($this->exists() === false) {
            return parent::changeNum($num);
        }

        $oldPage = $this->page();
        $oldRoot = $this->root();

        $newPage = parent::changeNum($num);
        $newRoot = $this->root($newPage);

        $this->moveDirectory($oldRoot, $newRoot);

        return $newPage;

    }

    public function changeSlug(string $slug)
    {
        if ($this->exists() === false) {
            return parent::changeSlug($slug);
        }

        $oldPage = $this->page();
        $oldRoot = $this->root();

        $newPage = parent::changeSlug($slug);
        $newRoot = $this->root($newPage);

        $this->moveDirectory($oldRoot, $newRoot);

        $this->media()->delete($oldPage);

        return $newPage;
    }

    public function changeTemplate(string $template)
    {
        if ($this->exists() === false) {
            return parent::changeTemplate($template);
        }

        $oldPage = $this->page();
        $oldFile = $this->base()->storage();

        $newPage = parent::changeTemplate($template);
        $newFile = $this->root() . '/' . $newPage->template() . '.' . $this->base()->extension();

        if (F::move($oldFile, $newFile) !== true) {
            throw new Exception('The text file could not be moved');
        }

        return $newPage;
    }

    public function children(): Children
    {

        $parent    = $this->page();
        $id        = $parent->id();
        $url       = $parent->url();
        $site      = $parent->site();
        $extension = $this->base()->extension();
        $children  = new Children([], $parent);

        foreach ($this->base()->children() as $slug => $props) {

            $page = Page::factory([
                'num'    => $props['num'],
                'parent' => $parent,
                'site'   => $site,
                'slug'   => $slug,
                'store'  => PageStore::class
            ]);

            $children->set($page->id(), $page);

        }

        return $children;

    }

    public function content()
    {
        return $this->base()->read();
    }

    public function createFile(File $file, string $source)
    {
        $file = $file->clone([
            'store' => FileStore::class
        ]);

        return $file->create($source);
    }

    public function createChild(Page $child)
    {
        if ($this->exists() === false) {
            return $child;
        }

        $parent = $this->page();
        $root   = $this->root() . '/' . $child->slug();

        // create the new page directory
        if (Dir::make($root) !== true) {
            throw new Exception('The page directory cannot be created');
        }

        // write the text file
        touch($root . '/' . $child->template() . '.txt');

        // attach the store
        $child = $child->clone([
            'store' => static::class
        ]);

        // write the content file
        return $child->update();
    }

    public function delete(): bool
    {
        // delete the content folder for this page
        return $this->base()->delete();
    }

    public function exists(): bool
    {
        return is_dir($this->base()->root()) === true;
    }

    public function files(): Files
    {
        $base      = $this->base();
        $page      = $this->page();
        $url       = $this->media()->url($page);
        $extension = $base->extension();
        $files     = new Files([], $page);

        foreach ($this->base()->files() as $filename => $props) {

            $file = new File([
                'filename' => $filename,
                'url'      => $url . '/' . $filename,
                'parent'   => $page,
                'store'    => FileStore::class
            ]);

            $files->set($file->id(), $file);

        }

        return $files;
    }

    public function id()
    {
        return $this->base()->root();
    }

    protected function moveDirectory(string $old, string $new): bool
    {
        if (Dir::move($old, $new) !== true) {
            throw new Exception('The directory could not be moved');
        }

        return true;
    }

    protected function parentRoot(): string
    {
        return dirname($this->root());
    }

    protected function root($page = null): string
    {

        if ($page === null) {
            if (is_string($this->root) === true) {
                return $this->root;
            }

            return $this->root = $this->kirby()->root('content') . '/' . $this->page()->diruri();
        }

        return $this->parentRoot() . '/' . $page->dirname();
    }

    public function template(): string
    {
        return $this->base()->type();
    }

    public function update(array $content = [], $form)
    {
        $page = parent::update($content, $form);

        if ($this->exists() === false) {
            return $page;
        }

        $this->base()->write($form->stringValues());

        return $page;
    }

}
