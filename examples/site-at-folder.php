<?php

require '../vendor/autoload.php';

use Kirby\Cms\Site;

$site = new Site([
    'url'   => 'https://getkirby.com',
    'root'  => __DIR__ . '/content',
]);

var_dump($site->title());
