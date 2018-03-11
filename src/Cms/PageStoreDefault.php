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
        ]);
    }

    public function changeSlug(string $slug)
    {
        return $this->page()->clone([
            'slug' => $slug,
            'url'  => rtrim(dirname($this->page()->url()), '/') . '/' . $slug
        ]);
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

    public function children()
    {
        return new Children([], $this->page());
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
