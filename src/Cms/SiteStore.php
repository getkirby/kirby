<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Data\Data;

class SiteStore
{

    protected $folder;
    protected $site;

    public function __construct(Site $site)
    {
        $this->folder = new Folder((string)$site->root());
        $this->site   = $site;
    }

    public function blueprint(): SiteBlueprint
    {
        return SiteBlueprint::load(App::instance()->root('blueprints') . '/site.yml');
    }

    public function children(): Pages
    {
        $url      = $this->site->url();
        $children = [];

        foreach ($this->folder->folders() as $info) {
            $children[] = Page::factory([
                'id'   => $info['slug'],
                'url'  => $url . '/' . $info['slug'],
                'root' => $info['root'],
                'num'  => $info['num'],
                'site' => $this->site
            ]);
        }

        return new Pages($children, $this);
    }

    public function content(): Content
    {
        $content = Data::read($this->site->root() . '/site.txt');
        return new Content($content, $this);
    }

    public function exists(): bool
    {
        return $this->folder->exists();
    }

    public function files(): Files
    {
        $url    = App::instance()->media()->url($this->site);
        $files  = [];

        foreach ($this->folder->files() as $info) {
            $files[] = new File([
                'id'     => $info['filename'],
                'url'    => $url . '/' . $info['filename'],
                'root'   => $info['root'],
                'parent' => $this->site
            ]);
        }

        return new Files($files, $this);
    }

    public function update(): bool
    {
        $content = $this->site->content()->update($content);

        Data::write($this->site->root() . '/site.txt', $content->toArray());

        return $this->site->setContent($content);
    }

}
