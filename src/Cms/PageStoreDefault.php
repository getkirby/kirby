<?php

namespace Kirby\Cms;

use Exception;

class PageStoreDefault extends Store
{

    public function blueprint()
    {
        $root = $this->kirby()->root('blueprints') . '/pages';

        try {
            return PageBlueprint::load($root . '/' . $this->page()->template() . '.yml', $this->page());
        } catch (Exception $e) {
            return PageBlueprint::load($root . '/default.yml', $this->page());
        }
    }

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

    public function children()
    {
        return new Children([], $this->page());
    }

    public function content()
    {
        return [];
    }

    public function create(Page $page)
    {
        return $page;
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

    public function update(array $content = [], $form)
    {
        return $this->page()->clone([
            'content' => $form->stringValues()
        ]);
    }

}
