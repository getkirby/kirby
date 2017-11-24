<?php

require '../vendor/autoload.php';

use Kirby\FileSystem\Folder;

$folder = new Folder(__DIR__);

var_dump($folder->exists());
var_dump($folder->root());
var_dump($folder->name());
var_dump($folder->files());
var_dump($folder->folders());
