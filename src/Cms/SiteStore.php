<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Util\Dir;

class SiteStore extends SiteStoreDefault
{

    const PAGE_STORE_CLASS = PageStore::class;
    const FILE_STORE_CLASS = FileStore::class;

    protected $base;

    public function base()
    {
        if (is_a($this->base, Base::class) === true) {
            return $this->base;
        }

        return $this->base = new Base([
            'extension' => 'txt',
            'root'      => $this->site()->root(),
            'type'      => 'site',
        ]);
    }

    public function children(): array
    {
        $site     = $this->site();
        $url      = $site->url();
        $children = [];

        foreach ($this->base()->children() as $slug => $props) {
            $children[] = $props + [
                'slug'  => $slug,
                'url'   => $url . '/' . $slug,
                'store' => static::PAGE_STORE_CLASS
            ];
        }

        return $children;
    }

    public function content()
    {
        return $this->base()->read();
    }

    public function drafts(): array
    {
        $site   = $this->site();
        $url    = $site->url();
        $drafts = [];
        $base   = new Base([
            'extension' => 'txt',
            'root'      => $site->root() . '/_drafts',
        ]);

        foreach ($base->children() as $slug => $props) {
            $drafts[] = [
                'num'    => $props['num'],
                'slug'   => $slug,
                'status' => 'draft',
                'url'    => $url . '/_drafts/' . $slug,
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
        $base  = $this->base();
        $site  = $this->site();
        $root  = $base->root();
        $url   = $site->kirby()->media()->url($site);
        $files = [];

        foreach ($this->base()->files() as $filename => $props) {
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
        return $this->base()->root();
    }

    public function root()
    {
        return $this->base()->root();
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

        $this->base()->write($site->content()->toArray());

        return $site;
    }

}
