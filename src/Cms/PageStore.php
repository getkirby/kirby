<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;
use Kirby\Exception\DuplicateException;
use Kirby\Exception\LogicException;
use Kirby\Toolkit\F;
use Throwable;

class PageStore extends PageStoreDefault
{
    const PAGE_STORE_CLASS = PageStore::class;
    const FILE_STORE_CLASS = FileStore::class;

    protected $inventory;
    protected $root;

    public function inventory()
    {
        return $this->inventory ?? $this->inventory = Dir::inventory($this->page()->root());
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

        Dir::remove($oldPage->mediaRoot());

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

    public function changeTemplate(string $template, array $data = [])
    {
        if ($this->exists() === false) {
            return parent::changeTemplate($template, $data);
        }

        $newPage = parent::changeTemplate($template, $data);
        $newFile = $newPage->root() . '/' . $newPage->template() . '.txt';
        $oldFile = $this->inventory()['content'];

        if (Data::write($newFile, $data) !== true) {
            throw new LogicException('The new text file could not be written');
        }

        if (F::remove($oldFile) !== true) {
            throw new LogicException('The old text file could not be removed');
        }

        return $newPage;
    }

    public function children(): array
    {
        $children = [];

        foreach ($this->inventory()['children'] as $props) {
            $props['store'] = static::PAGE_STORE_CLASS;
            $children[] = $props;
        }

        return $children;
    }

    public function content(): array
    {
        try {
            return Data::read($this->inventory()['content']);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function create()
    {
        $page = $this->page();

        if ($this->exists() === true) {
            return $page;
        }

        $root = $page->root();

        if (is_dir($root) === true) {
            throw new DuplicateException([
                'key'  => 'page.draft.duplicate',
                'data' => ['slug' => $page->slug()]
            ]);
        }

        // create the new page directory
        if (Dir::make($root) !== true) {
            throw new LogicException('The page directory for "' . $page->slug() . '" cannot be created');
        }

        // write the text file
        touch($root . '/' . $page->template() . '.txt');

        // reset the inventory
        $this->inventory = null;

        // write the content file
        return $page->update(null, false);
    }

    public function delete(): bool
    {
        $page = $this->page();

        // delete all public media files
        Dir::remove($page->mediaRoot());

        // delete the content folder for this page
        Dir::remove($page->root());

        // if the page is a draft and the _drafts folder
        // is now empty. clean it up.
        if ($page->isDraft() === true) {
            $draftsDir = dirname($page->root());

            if (Dir::isEmpty($draftsDir) === true) {
                Dir::remove($draftsDir);
            }
        }

        return true;
    }

    public function drafts(): array
    {
        $drafts    = [];
        $parent    = $this->page();
        $inventory = Dir::inventory($parent->root() . '/_drafts');

        foreach ($inventory['children'] as $props) {
            $drafts[] = $props + [
                'status' => 'draft',
                'url'    => $parent->url() . '/_drafts/' . $props['slug'],
                'store'  => static::PAGE_STORE_CLASS
            ];
        }

        return $drafts;
    }

    public function exists(): bool
    {
        return is_dir($this->page()->root()) === true;
    }

    public function files(): array
    {
        $page      = $this->page();
        $url       = $page->mediaUrl();
        $files     = [];

        foreach ($this->inventory()['files'] as $filename => $props) {
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
        return $this->page()->root();
    }

    public function modified()
    {
        return filemtime($this->inventory()['content']);
    }

    protected function moveDirectory(string $old, string $new): bool
    {
        $parent = dirname($new);

        if (Dir::make($parent, true) !== true) {
            throw new LogicException('The parent directory cannot be created');
        }

        if (Dir::move($old, $new) !== true) {
            throw new LogicException('The page directory cannot be moved');
        }

        return true;
    }

    protected function publishDraft()
    {
        $draft = $this->page();
        $root  = $draft->parentModel()->root() . '/' . $draft->slug();

        if ($draft->isPage() === false) {
            throw new LogicException([
                'key'  => 'page.draft.invalid',
                'data' => ['slug' => $draft->slug()]
            ]);
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
        return $this->inventory()['template'];
    }

    public function update(array $values = [], array $strings = [])
    {
        $page = parent::update($values, $strings);

        if ($this->exists() === false) {
            return $page;
        }

        Data::write($this->inventory()['content'], $page->content()->toArray());

        return $page;
    }
}
