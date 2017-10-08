<?php

require '../vendor/autoload.php';

use Kirby\Cms\Site;

$site = new Site([
    'root' => __DIR__ . '/content',
    'url'  => 'https://getkirby.com'
]);

$result = $site->find('projects')->children()->query([
    'filterBy' => [
        'year'  => ['>=' => 2014],
    ],
    'paginate' => [
        'page'  => 1,
        'limit' => 1
    ],
    'sortBy' => 'title desc'
]);

var_dump($result->keys());
