<?php

namespace Kirby\Cms;

class SiteStoreDefault extends Store
{

    public function blueprint()
    {
        return SiteBlueprint::load($this->kirby()->root('blueprints') . '/site.yml', $this->site());
    }

    public function children()
    {
        return new Pages([], $this->site());
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

    public function exists(): bool
    {
        return false;
    }

    public function files()
    {
        return new Files([], $this->site());
    }

    public function id()
    {
        return null;
    }

    public function site()
    {
        return $this->model;
    }

    public function update(array $content = [], $form)
    {
        return $this->site()->clone([
            'content' => $form->stringValues()
        ]);
    }

}
