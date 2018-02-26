<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Base\Base;
use Kirby\Util\Dir;

class SiteStore extends SiteStoreDefault
{

    protected $base;

    public function base()
    {
        if (is_a($this->base, Base::class) === true) {
            return $this->base;
        }

        return $this->base = new Base([
            'extension' => 'txt',
            'root'      => $this->kirby()->root('content'),
            'type'      => 'site',
        ]);
    }

    public function children()
    {

        $site      = $this->site();
        $url       = $site->url();
        $children  = new Pages([], $site);
        $extension = $this->base()->extension();

        foreach ($this->base()->children() as $slug => $props) {

            $props['slug']  = $slug;
            $props['url']   = $url . '/' . $slug;
            $props['site']  = $site;
            $props['store'] = PageStore::class;

            $page = Page::factory($props);

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

    public function exists(): bool
    {
        return is_dir($this->root()) === true;
    }

    public function files()
    {

        $base      = $this->base();
        $site      = $this->site();
        $root      = $base->root();
        $extension = $base->extension();
        $url       = $site->kirby()->media()->url($site);
        $files     = new Files([], $site);

        foreach ($this->base()->files() as $filename => $props) {

            $file = new File([
                'filename' => $filename,
                'parent'   => $site,
                'store'    => FileStore::class,
                'url'      => $url . '/' . $filename,
            ]);

            $files->set($file->id(), $file);

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

    public function update(array $content = [], $form)
    {
        $site = parent::update($content, $form);

        if ($this->exists() === false) {
            return $site;
        }

        $this->base()->write($form->stringValues());

        return $site;
    }

}
