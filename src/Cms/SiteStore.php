<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Throwable;

class SiteStore extends SiteStoreDefault
{
    const PAGE_STORE_CLASS = PageStore::class;
    const FILE_STORE_CLASS = FileStore::class;

    protected $inventory;

    public function inventory()
    {
        return $this->inventory ?? $this->inventory = Dir::inventory($this->site()->root());
    }

    public function children(): array
    {
        $site     = $this->site();
        $url      = $site->url();
        $children = [];

        foreach ($this->inventory()['children'] as $props) {
            $children[] = $props + [
                'url'   => $url . '/' . $props['slug'],
                'store' => static::PAGE_STORE_CLASS
            ];
        }

        return $children;
    }

    public function content()
    {
        try {
            return Data::read($this->inventory()['content']);
        } catch (Throwable $e) {
            return [];
        }
    }

    public function drafts(): array
    {
        $site      = $this->site();
        $url       = $site->url();
        $drafts    = [];
        $inventory = Dir::inventory($site->root() . '/_drafts');

        foreach ($inventory['children'] as $props) {
            $drafts[] = [
                'num'    => $props['num'],
                'slug'   => $props['slug'],
                'status' => 'draft',
                'url'    => $url . '/_drafts/' . $props['slug'],
                'store'  => static::PAGE_STORE_CLASS
            ];
        }

        return $drafts;
    }

    public function exists(): bool
    {
        return is_dir($this->root()) === true;
    }

    public function files(): array
    {
        $site  = $this->site();
        $root  = $site->root();
        $url   = $site->mediaUrl();
        $files = [];

        foreach ($this->inventory()['files'] as $filename => $props) {
            $file = [
                'filename' => $filename,
                'store'    => static::FILE_STORE_CLASS,
                'url'      => $url . '/' . $filename,
            ];

            $files[] = $file;
        }

        return $files;
    }

    public function id()
    {
        return $this->root();
    }

    public function root()
    {
        return $this->site()->root();
    }

    public function site()
    {
        return $this->model;
    }

    public function update(array $values = [], array $strings = [])
    {
        $site = parent::update($values, $strings);

        if ($this->exists() === false) {
            return $site;
        }

        Data::write($this->inventory()['content'], $site->content()->toArray());

        return $site;
    }
}
