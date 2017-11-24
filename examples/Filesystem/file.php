<?php

require '../vendor/autoload.php';

use Kirby\FileSystem\File;

$file = new File(__FILE__);

var_dump($file->exists());
var_dump($file->root());
var_dump($file->filename());
var_dump($file->name());
var_dump($file->extension());
var_dump($file->type());
var_dump($file->size());
var_dump($file->niceSize());
var_dump($file->mime());
