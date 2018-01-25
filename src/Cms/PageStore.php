<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;

class PageStore
{

    protected $folder;
    protected $page;

    public function __construct(Page $page)
    {
        $this->folder = new Folder((string)$page->root());
        $this->page   = $page;
    }

    public function blueprint(): PageBlueprint
    {
        $root = App::instance()->root('blueprints') . '/pages';

        try {
            return PageBlueprint::load($root . '/' . $this->page->template() . '.yml');
        } catch (Exception $e) {
            return PageBlueprint::load($root . '/default.yml');
        }
    }

    public function children(): Children
    {

        $id       = $this->page->id();
        $url      = $this->page->url();
        $site     = $this->page->site();
        $children = [];

        foreach ($this->folder->folders() as $info) {
            $children[] = Page::factory([
                'id'     => $id  . '/' . $info['slug'],
                'num'    => $info['num'],
                'parent' => $this->page,
                'root'   => $info['root'],
                'site'   => $site,
                'url'    => $url . '/' . $info['slug'],
            ]);
        }

        return new Children($children, $this->page);

    }

    public function content(): Content
    {
        $content = [];

        if ($db = $this->folder->db()) {
            $content = Data::read($this->folder->db());
        }

        return new Content($content, $this->page);
    }

    public function exists(): bool
    {
        return is_dir($this->page->root());
    }

    public function files(): Files
    {
        $id     = $this->page->id();
        $site   = $this->page->site();
        $url    = App::instance()->media()->url($this->page);
        $files  = [];

        foreach ($this->folder->files() as $info) {
            $files[] = new File([
                'id'     => $id  . '/' . $info['filename'],
                'url'    => $url . '/' . $info['filename'],
                'root'   => $info['root'],
                'parent' => $this->page
            ]);
        }

        return new Files($files, $this->page);
    }

    public function delete(): bool
    {
        // delete all public files for this page
        App::instance()->media()->delete($page);

        // delete the content folder for this page
        $this->folder->delete();
        return true;
    }

    public function template(): string
    {
        return pathinfo($this->folder->db(), PATHINFO_FILENAME);
    }

    public function update(array $content): bool
    {
        $content = $this->page->content()->update($content);

        Data::write($this->folder->db(), $content->toArray());

        return $this->page->setContent($content);
    }

}
