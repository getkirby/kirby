<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\Dir;
use Kirby\Util\F;

class PageStore extends PageStoreDefault
{

    const PAGE_STORE_CLASS = PageStore::class;
    const FILE_STORE_CLASS = FileStore::class;

    protected $base;
    protected $root;

    public function base()
    {
        if (is_a($this->base, Base::class) === true) {
            return $this->base;
        }

        return $this->base = new Base([
            'extension' => 'txt',
            'root'      => $this->page()->root()
        ]);
    }

    public function changeNum(int $num = null)
    {
        if ($this->exists() === false) {
            return parent::changeNum($num);
        }

        $oldPage = $this->page();
        $oldRoot = $oldPage->root();

        $newPage = parent::changeNum($num);
        $newRoot = $newPage->root();

        $this->moveDirectory($oldRoot, $newRoot);

        return $newPage;
    }

    public function changeSlug(string $slug)
    {
        if ($this->exists() === false) {
            return parent::changeSlug($slug);
        }

        $oldPage = $this->page();
        $oldRoot = $oldPage->root();

        $newPage = parent::changeSlug($slug);
        $newRoot = $newPage->root();

        $this->moveDirectory($oldRoot, $newRoot);

        $this->media()->delete($oldPage);

        return $newPage;
    }

    protected function changeStatusToDraft()
    {
        $oldPage = $this->page();
        $newPage = parent::changeStatusToDraft();

        $this->moveDirectory($oldPage->root(), $newPage->root());

        return $newPage;
    }

    protected function changeStatusToListed(int $position)
    {
        // publish the draft first
        if ($this->page()->isDraft() === true) {
            $this->model = $this->publishDraft();
        }

        return $this->changeNum($position);
    }

    protected function changeStatusToUnlisted()
    {
        // publish the draft first
        if ($this->page()->isDraft() === true) {
            $this->page = $this->publishDraft();
        }

        return $this->changeNum(null);
    }

    public function changeTemplate(string $template)
    {
        if ($this->exists() === false) {
            return parent::changeTemplate($template);
        }

        $oldPage = $this->page();
        $oldFile = $this->base()->storage();

        $newPage = parent::changeTemplate($template);
        $newFile = $newPage->root() . '/' . $newPage->template() . '.' . $this->base()->extension();

        if (F::move($oldFile, $newFile) !== true) {
            throw new Exception('The text file could not be moved');
        }

        return $newPage;
    }

    public function children(): array
    {
        $children = [];

        foreach ($this->base()->children() as $slug => $props) {
            $children[] = $props + [
                'slug'   => $slug,
                'store'  => static::PAGE_STORE_CLASS
            ];
        }

        return $children;
    }

    public function content()
    {
        return $this->base()->read();
    }

    public function create()
    {
        $page = $this->page();

        if ($this->exists() === true) {
            return $page;
        }

        $root = $page->root();

        if (is_dir($root) === true) {
            throw new Exception('The draft already exists');
        }

        // create the new page directory
        if (Dir::make($root) !== true) {
            throw new Exception('The page directory cannot be created');
        }

        // write the text file
        touch($root . '/' . $page->template() . '.txt');

        // write the content file
        return $page->update(null, false);
    }

    public function delete(): bool
    {
        // delete the content folder for this page
        Dir::remove($this->page()->root());

        // if the page is a draft and the _drafts folder
        // is now empty. clean it up.
        if ($this->page()->isDraft() === true) {
            $draftsDir = dirname($this->page()->root());

            if (Dir::isEmpty($draftsDir) === true) {
                Dir::remove($draftsDir);
            }
        }

        return true;
    }

    public function drafts(): array
    {
        $drafts = [];
        $parent = $this->page();
        $base   = new Base([
            'extension' => 'txt',
            'root'      => $parent->root() . '/_drafts',
        ]);

        foreach ($base->children() as $slug => $props) {
            $drafts[] = $props + [
                'slug'   => $slug,
                'status' => 'draft',
                'url'    => $parent->url() . '/_drafts/' . $slug,
                'store'  => static::PAGE_STORE_CLASS
            ];
        }

        return $drafts;
    }

    public function exists(): bool
    {
        return is_dir($this->base()->root()) === true;
    }

    public function files(): array
    {
        $base      = $this->base();
        $page      = $this->page();
        $url       = $this->media()->url($page);
        $extension = $base->extension();
        $files     = [];

        foreach ($this->base()->files() as $filename => $props) {
            $files[] = [
                'filename' => $filename,
                'url'      => $url . '/' . $filename,
                'store'    => static::FILE_STORE_CLASS
            ];
        }

        return $files;
    }

    public function id(): string
    {
        return $this->base()->root();
    }

    public function modified()
    {
        return filemtime($this->base()->storage());
    }

    protected function moveDirectory(string $old, string $new): bool
    {
        $parent = dirname($new);

        if (Dir::make($parent, true) !== true) {
            throw new Exception('The parent directory could not be created');
        }

        if (Dir::move($old, $new) !== true) {
            throw new Exception('The directory could not be moved');
        }

        return true;
    }

    protected function publishDraft()
    {
        $draft = $this->page();
        $root  = $draft->parentModel()->root() . '/' . $draft->slug();

        if ($draft->isPage() === false) {
            throw new Exception('The page is not a draft');
        }

        $this->moveDirectory($draft->root(), $root);

        // Get the draft folder and check if there are any other drafts
        // left. Otherwise delete it.
        $draftDir = dirname($draft->root());

        if (Dir::isEmpty($draftDir) === true) {
            Dir::remove($draftDir);
        }

        return $draft->clone([], Page::class);
    }

    public function template(): string
    {
        return $this->base()->type();
    }

    public function update(array $values = [], array $strings = [])
    {
        $page = parent::update($values, $strings);

        if ($this->exists() === false) {
            return $page;
        }

        $this->base()->write($page->content()->toArray());

        return $page;
    }

}
