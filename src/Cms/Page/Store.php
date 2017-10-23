<?php

namespace Kirby\Cms\Page;

use Exception;
use Kirby\Cms\Page;
use Kirby\Cms\Site;
use Kirby\Data\Data;
use Kirby\FileSystem\Folder;

class Store
{

    protected $page;
    protected $root;
    protected $folder;
    protected $db;

    public function __construct(Page $page, $attributes = [])
    {
        $this->page = $page;
        $this->root = $attributes['root'] ?? null;
    }

    public function folder(): Folder
    {

        if (is_a($this->folder, Folder::class)) {
            return $this->folder;
        }

        if ($this->root === null) {
            throw new Exception('The root for the page is required to initialize the store');
        }

        return $this->folder = new Folder($this->root);

    }

    public function children()
    {

        if ($this->folder() === null) {
            return [];
        }

        $id   = $this->page->id();
        $url  = $this->page->url();
        $site = $this->page->site();

        $children = [];

        foreach ($this->folder()->folders() as $root) {

            $basename = basename($root);

            if (preg_match('!^([0-9]+?)-(.*)$!', $basename, $matches)) {
                $num  = $matches[1];
                $slug = $matches[2];
            } else {
                $num  = null;
                $slug = $basename;
            }

            $children[] = [
                'id'     => $id  . '/' . $slug,
                'url'    => $url . '/' . $slug,
                'root'   => $root,
                'num'    => $num,
                'site'   => $site,
                'parent' => $this->page,
            ];

        }

        return $children;

    }

    public function files(): array
    {

        $id    = $this->page->id();
        $site  = $this->page->site();
        $files = [];

        // TODO: make this less smart
        if (is_a($site, Site::class)) {
            $url = $site->url() . '/files/' . $this->page->id();
        } else {
            $url = $this->page->url();
        }

        foreach ($this->folder()->files() as $root) {

            if (strtolower(pathinfo($root, PATHINFO_EXTENSION)) === 'txt') {
                continue;
            }

            $files[] = [
                'id'   => $id . '/' . $filename = basename($root),
                'root' => $root,
                'url'  => $url . '/' . $filename,
                'page' => $this->page,
                'site' => $site
            ];

        }

        return $files;

    }

    public function template(): string
    {
        return pathinfo($this->db(), PATHINFO_FILENAME);
    }

    protected function db()
    {

        if (is_string($this->db)) {
            return $this->db;
        }

        foreach ($this->folder()->files() as $root) {

            if (strtolower(pathinfo($root, PATHINFO_EXTENSION)) !== 'txt') {
                continue;
            }

            if (preg_match('!\.([a-z]{2,4})\.txt$!i', $root) !== 0) {
                continue;
            }

            $this->db = $root;
            break;

        }

        return $this->db;

    }

    public function exists(): bool
    {
        return $this->folder()->exists() && file_exists($this->db());
    }

    public function create(): bool
    {
        // the template is required to create the db file
        $template = $this->page->template();

        if ($template === null) {
            throw new Exception('A template has to be set in order to create pages');
        }

        if ($this->folder()->parent()->exists() === false) {
            throw new Exception('The parent directory for the page does not exist');
        }

        // create the folder
        $this->folder()->make();

        // create the link to the data file
        $this->db = $this->folder()->root() . '/' . $this->page->template() . '.txt';

        // store the data
        $this->write($this->page->content()->data());

        return true;
    }

    public function read(): array
    {
        if (file_exists($this->db()) === false) {
            return [];
        }

        try {
            return Data::read($this->db());
        } catch (Exception $e) {
            return [];
        }
    }

    public function write($data = []): bool
    {
        return Data::write($this->db(), $data);
    }

    public function move($slug)
    {

        $name = implode('-', array_filter([
            $this->page->num(),
            $slug
        ]));

        $oldRoot = $this->root;
        $newRoot = dirname($this->root) . '/' . $name;

        rename($oldRoot, $newRoot);

        $this->db     = null;
        $this->root   = $newRoot;
        $this->folder = new Folder($this->root);

        return $this->root;

    }

    public function unlisted()
    {

        if ($this->page->isVisible() === false) {
            return $this->root;
        }

        $oldRoot = $this->root;
        $newRoot = dirname($this->root) . '/' . $this->page->slug();

        rename($oldRoot, $newRoot);

        $this->db     = null;
        $this->root   = $newRoot;
        $this->folder = new Folder($this->root);

        return $this->root;

    }

    public function listed(int $position)
    {

        $oldRoot = $this->root;
        $newRoot = dirname($this->root) . '/' . $position . '-' . $this->page->slug();

        rename($oldRoot, $newRoot);

        $this->db     = null;
        $this->root   = $newRoot;
        $this->folder = new Folder($this->root);

        return $this->root;

    }

    public function delete()
    {
        return $this->folder()->delete();
    }

}
