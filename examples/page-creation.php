<?php

require '../vendor/autoload.php';

use Kirby\Cms\Page;
use Kirby\Cms\Site;

$page = new Page([
    'id'      => 'my-page',
    'url'     => 'http://google.com/my-page',
    'root'    => 'content',
    'content' => [
        'title' => 'Awesome'
    ],
]);


var_dump($page->files());
