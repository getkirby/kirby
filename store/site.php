<?php

use Kirby\Cms\Content;
use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Cms\Folder;
use Kirby\Cms\Page;
use Kirby\Cms\Pages;
use Kirby\Cms\Site;
use Kirby\Cms\SiteBlueprint;
use Kirby\Data\Data;

return [
    'site.blueprint' => function (Site $site): SiteBlueprint {
        return SiteBlueprint::load($this->kirby()->root('blueprints') . '/site.yml');
    },
    'site.children' => function (Site $site): Pages {

        $url      = $site->url();
        $folder   = new Folder($site->root());
        $children = [];

        foreach ($folder->folders() as $info) {
            $children[] = Page::factory([
                'id'   => $info['slug'],
                'url'  => $url . '/' . $info['slug'],
                'root' => $info['root'],
                'num'  => $info['num']
            ]);
        }

        return new Pages($children, $site);

    },
    'site.content' => function (Site $site): Content {

        $content = Data::read($site->root() . '/site.txt');

        return new Content($content, $site);

    },
    'site.files' => function (Site $site): Files {

        $folder = new Folder($site->root());
        $url    = $this->media()->url($site);
        $files  = [];

        foreach ($folder->files() as $info) {
            $files[] = new File([
                'id'   => $info['filename'],
                'url'  => $url . '/' . $info['filename'],
                'root' => $info['root']
            ]);
        }

        return new Files($files, $site);

    },
    'site.update' => function (Site $site, array $content) {

        $content = $site->content()->update($content);

        Data::write($site->root() . '/site.txt', $content->toArray());

        return $site->set('content', $content);

    },
];
