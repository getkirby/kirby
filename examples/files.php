<?php

require '../vendor/autoload.php';

use Kirby\Cms\Files;

$files = new Files([
    [
        'id'   => 'superfile.jpg',
        'url'  => 'https://getkirby.com/projects/project-a/superfile.jpg',
        'root' => __DIR__ . '/superfile.jpg',
        'meta' => [
            'caption' => 'Super'
        ]
    ],
    [
        'id'   => 'and-another-one.jpg',
        'url'  => 'https://getkirby.com/projects/project-a/and-another-one.jpg',
        'root' => __DIR__ . '/and-another-one.jpg',
        'meta' => [
            'caption' => 'Awesome'
        ]
    ]
]);

var_dump($files);
