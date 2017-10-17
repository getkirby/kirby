<?php

require '../vendor/autoload.php';

use Kirby\Cms\App;
use Kirby\Cms\File;
use Kirby\Image\Darkroom\GdLib as Darkroom;


$app = new App([
    'root' => [
        'files' => __DIR__ . '/files'
    ],
    'url' => [
        'files' => 'http://localhost/kirby/v3/starterkit/kirby/examples/files'
    ],
    'darkroom' => new Darkroom([
        'quality' => 70
    ])
]);

$file = new File([
    'id'   => 'closeup.jpg',
    'url'  => 'https://getkirby.com/closeup.jpg',
    'root' => __DIR__ . '/closeup.jpg'
]);

$thumb = $file->crop(200);

var_dump($thumb);
