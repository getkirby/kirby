<?php

namespace Kirby\Cms\Site;

use Exception;
use Kirby\Cms\Site;
use Kirby\Data\Data;
use Kirby\FileSystem\Folder;

class Store
{

    protected $site;
    protected $root;
    protected $folder;
    protected $db;

    public function __construct(Site $site, array $attributes)
    {
        $this->site   = $site;
        $this->root   = $site->root();
        $this->db     = $this->root . '/site.txt';
    }

    public function folder(): Folder
    {

        if (is_a($this->folder, Folder::class)) {
            return $this->folder;
        }

        if ($this->root === null) {
            throw new Exception('The root for the site is required to initialize the store');
        }

        return $this->folder = new Folder($this->root);

    }


    public function children()
    {

        if ($this->folder() === null) {
            return [];
        }

        $url = $this->site->url();

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
                'id'     => $slug,
                'url'    => $url . '/' . $slug,
                'root'   => $root,
                'slug'   => $slug,
                'num'    => $num,
                'site'   => $this->site
            ];

        }

        return $children;

    }

    public function files(): array
    {

        $url = $this->site->url();

        $files = [];

        foreach ($this->folder()->files() as $root) {

            if (strtolower(pathinfo($root, PATHINFO_EXTENSION)) === 'txt') {
                continue;
            }

            $files[] = [
                'id'   => $filename = basename($root),
                'root' => $root,
                'url'  => $url . '/' . $filename,
                'site' => $this->site
            ];

        }

        return $files;

    }

    protected function db(): string
    {
        return $this->db;
    }

    public function read(): array
    {
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

}
