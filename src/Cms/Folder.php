<?php

namespace Kirby\Cms;

use Kirby\FileSystem\Folder as BaseFolder;

class Folder extends BaseFolder
{

    public function folders(): array
    {

        $folders = [];

        foreach (parent::folders() as $root) {

            $basename = basename($root);

            if (substr($basename, 0, 1) === '.') {
                continue;
            }

            if (preg_match('!^([0-9]+?)-(.*)$!', $basename, $matches)) {
                $num  = intval($matches[1]);
                $slug = $matches[2];
            } else {
                $num  = null;
                $slug = $basename;
            }

            $folders[] = [
                'root' => $root,
                'slug' => $slug,
                'num'  => $num
            ];

        }

        return $folders;

    }

    public function db()
    {
        foreach (glob($this->root . '/*.txt') as $root) {
            if (preg_match('!\.([a-z]{2,4})\.txt$!i', $root) !== 0) {
                continue;
            }
            return $root;
        }

        return $this->root . '/default.txt';

    }

    public function files(): array
    {

        $files = [];

        foreach (parent::files() as $root) {

            if (strtolower(pathinfo($root, PATHINFO_EXTENSION)) === 'txt') {
                continue;
            }

            $files[] = [
                'filename' => basename($root),
                'root'     => $root,
            ];
        }

        return $files;

    }

}
