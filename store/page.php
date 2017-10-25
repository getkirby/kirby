<?php

use Kirby\Cms\Children;
use Kirby\Cms\Content;
use Kirby\Cms\File;
use Kirby\Cms\Files;
use Kirby\Cms\Folder;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Data\Data;

return [
    'page.change.slug' => function (Page $page, string $slug): Page {

        $newName = implode('-', array_filter([
            $page->num(),
            $slug
        ]));

        $oldRoot = $page->root();
        $newRoot = dirname($page->root()) . '/' . $newName;

        if ($parent = $page->parent()) {
            $newId  = $parent->id()  . '/' . $slug;
            $newUrl = $parent->url() . '/' . $slug;
        } else {
            $newId  = ltrim(dirname($this->id())  . '/' . $slug, './');
            $newUrl = ltrim(dirname($this->url()) . '/' . $slug, './');
        }

        // delete all public files for this page
        $this->media()->delete($page);

        rename($oldRoot, $newRoot);

        return $page->clone([
            'id'     => $newId,
            'root'   => $newRoot,
            'url'    => $newUrl,
        ]);

    },
    'page.change.template' => function (Page $page, string $template): Page {

        $folder  = new Folder($page->root());
        $newRoot = $page->root() . '/' . $template . '.txt';
        $oldRoot = $folder->db();

        rename($oldRoot, $newRoot);

        return $page->clone();

    },
    'page.change.status' => function (Page $page, string $status, int $position = null): Page {

        switch ($status) {
            case 'draft':
                throw new Exception('Not yet implemented');
            case 'unlisted':
                if ($page->isVisible() === false) {
                    return $page;
                }

                $oldRoot = $page->root();
                $newRoot = dirname($page->root()) . '/' . $page->slug();

                rename($oldRoot, $newRoot);
                return $page->clone(['root' => $newRoot]);
                break;
            case 'listed':

                if ($page->num() === $position) {
                    return $page;
                }

                $oldRoot = $page->root();
                $newRoot = dirname($page->root()) . '/' . $position . '-' . $page->slug();

                rename($oldRoot, $newRoot);
                return $page->clone(['root' => $newRoot]);
                break;
            default:
                throw new Exception('Invalid status type');
        }

    },
    'page.children' => function (Page $page) {

        $id       = $page->id();
        $url      = $page->url();
        $folder   = new Folder($page->root());
        $children = [];

        foreach ($folder->folders() as $info) {
            $children[] = new Page([
                'id'     => $id  . '/' . $info['slug'],
                'url'    => $url . '/' . $info['slug'],
                'root'   => $info['root'],
                'num'    => $info['num'],
                'parent' => $page
            ]);
        }

        return new Children($children, $page);

    },
    'page.content' => function (Page $page): Content {

        $folder  = new Folder($page->root());
        $content = Data::read($folder->db());

        return new Content($content, $page);

    },
    'page.create' => function (Page $parent = null, string $slug, string $template, array $content = []): Page {

        if ($parent) {
            $page = new Page([
                'parent'   => $parent,
                'id'       => $parent->id() . '/' . $slug,
                'root'     => $parent->root() . '/' . $slug,
                'template' => $template
            ]);
        } else {
            $page = new Page([
                'id'       => $slug,
                'root'     => $this->site()->root() . '/' . $slug,
                'template' => $template
            ]);
        }

        // create the page folder
        $folder = new Folder($page->root());
        $folder->make(true);

        // create the data file
        Data::write($folder->root() . '/' . $template . '.txt', $content);

        return $page;

    },
    'page.exists' => function (Page $page): bool {
        return is_dir($page->root());
    },
    'page.files' => function (Page $page): Files {

        $id     = $page->id();
        $site   = $page->site();
        $folder = new Folder($page->root());
        $url    = $this->media()->url($page);
        $files  = [];

        foreach ($folder->files() as $info) {
            $files[] = new File([
                'id'   => $id  . '/' . $info['filename'],
                'url'  => $url . '/' . $info['filename'],
                'root' => $info['root'],
                'page' => $page
            ]);
        }

        return new Files($files, $page);

    },
    'page.delete' => function (Page $page): bool {
        // delete all public files for this page
        $this->media()->delete($page);

        // delete the content folder for this page
        $folder = new Folder($page->root());
        $folder->delete();
        return true;
    },
    'page.template' => function (Page $page) {
        $folder = new Folder($page->root());
        return pathinfo($folder->db(), PATHINFO_FILENAME);
    },
    'page.update' => function (Page $page, array $content): Page {

        $folder  = new Folder($page->root());
        $content = $page->content()->update($content);

        Data::write($folder->db(), $content->toArray());

        return $page->set('content', $content);

    },
];
