<?php

use Kirby\FileSystem\Folder;
use Kirby\Data\Data;

return function ($root) {

    $folder = new Folder($root);
    $result = [];

    foreach ($folder->files() as $root) {
        $name = pathinfo($root, PATHINFO_FILENAME);
        $result[$name] = ['name' => $name] + Data::read($root);
    }

    return $result;

};
