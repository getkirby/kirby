<?php

require '../vendor/autoload.php';

use Kirby\Cms\File;

$file = new File([
    'id'   => 'creative-tools.jpg',
    'url'  => 'https://getkirby.com/creative-tools.jpg',
    'root' => __DIR__ . '/creative-tools.jpg',
]);

var_dump($file->rename('creative-tool'));
