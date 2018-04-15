<?php

namespace Kirby\Cms;

class SiteStoreDefault extends Store
{
    const PAGE_STORE_CLASS = PageStoreDefault::class;
    const FILE_STORE_CLASS = FileStoreDefault::class;

    public function changeTitle(string $title)
    {
        return $this->update($data = ['title' => $title], $data);
    }

    public function children(): array
    {
        return [];
    }

    public function content()
    {
        return [];
    }

    public function createChild(Page $child)
    {
        return $child;
    }

    public function createFile(File $file, string $source)
    {
        return $file;
    }

    public function drafts(): array
    {
        return [];
    }

    public function exists(): bool
    {
        return false;
    }

    public function files(): array
    {
        return [];
    }

    public function id()
    {
        return null;
    }

    public function site()
    {
        return $this->model;
    }

    public function update(array $values = [], array $strings = [])
    {
        return $this->site()->clone([
            'content' => $this->site()->content()->update($strings)->toArray()
        ]);
    }
}
