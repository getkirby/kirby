<?php

use Kirby\Cms\Blueprint;
use Kirby\FileSystem\Folder;

return function ($root) {

    $folder = new Folder($root);
    $result = [];

    foreach ($folder->files() as $root) {

        $name      = pathinfo($root, PATHINFO_FILENAME);
        $blueprint = new Blueprint($folder->root(), $name);

        $result[$blueprint->name()] = $blueprint->toArray();
    }

    return $result;

};
