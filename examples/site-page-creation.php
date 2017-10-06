<?php

require '../vendor/autoload.php';

use Kirby\Cms\Page;
use Kirby\Cms\Site;

$site = new Site([
    'root' => __DIR__ . '/content',
    'url'  => 'https://getkirby.com'
]);

$page = $site->child([
    'slug'     => 'test',
    'template' => 'test',
    'content'  => [
        'title' => 'Awesome',
    ]
]);

var_dump($page);
