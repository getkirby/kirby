<?php

require '../vendor/autoload.php';

use Kirby\Cms\File;

$file = new File([
    'id'   => 'superfile.jpg',
    'url'  => 'https://getkirby.com/projects/project-a/superfile.jpg',
    'root' => __DIR__ . '/superfile.jpg',
    'meta' => [
        'caption' => 'Nice Image'
    ]
]);

var_dump($file);
