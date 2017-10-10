<?php

use Kirby\FileSystem\Folder;
use Kirby\Data\Data;

return function ($root) {

    $blueprint = require __DIR__ . '/blueprint.php';
    $folder    = new Folder($root);
    $result    = [];

    foreach ($folder->files() as $root) {
        $data = $blueprint($root);
        $result[$data['name']] = $data;
    }

    return $result;

};
