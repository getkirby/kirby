<?php

namespace Kirby\Cms;

use Exception;

class PageStoreDefault extends Store
{

    const PAGE_STORE_CLASS = PageStoreDefault::class;
    const FILE_STORE_CLASS = FileStoreDefault::class;

    public function changeNum(int $num = null)
    {
        return $this->page()->clone([
            'num' => $num,
        ], Page::class);
    }

    public function changeSlug(string $slug)
    {
        return $this->page()->clone([
            'slug' => $slug,
            'url'  => rtrim(dirname($this->page()->url()), '/') . '/' . $slug
        ]);
    }

    public function changeStatus(string $status, int $position = null)
    {
        switch ($status) {
            case 'draft':
                return $this->changeStatusToDraft();
            case 'listed':
                return $this->changeStatusToListed($position);
            case 'unlisted':
                return $this->changeStatusToUnlisted();
            default:
                throw new Exception('Invalid status');
        }
    }

    protected function changeStatusToDraft()
    {
        return $this->page()->clone(['num' => null], PageDraft::class);
    }

    protected function changeStatusToListed(int $position)
    {
        return $this->changeNum($position);
    }

    protected function changeStatusToUnlisted()
    {
        return $this->changeNum(null);
    }

    public function changeTemplate(string $template)
    {
        return $this->page()->clone([
            'template' => $template
        ]);
    }

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

    public function create()
    {
        return $this->page();
    }

    public function delete(): bool
    {
        throw new Exception('This page cannot be deleted');
    }

    public function drafts(): array
    {
        return [];
    }

    public function exists(): bool
    {
        return false;
    }

    public function files()
    {
        return new Files([], $this->page());
    }

    public function id(): string
    {
        return $this->page()->id();
    }

    public function media()
    {
        return $this->kirby()->media();
    }

    public function page()
    {
        return $this->model;
    }

    public function publish()
    {
        return $this->page();
    }

    public function template(): string
    {
        return 'default';
    }

    public function update(array $values = [], array $strings = [])
    {
        return $this->page()->clone([
            'content' => $this->page()->content()->update($strings)->toArray()
        ]);
    }

}
