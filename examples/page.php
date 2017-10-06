<?php

require '../vendor/autoload.php';

use Kirby\Cms\Page;



$page = new Page([
    'id'           => 'projects',
    'url'          => 'https://getkirby.com/projects',
    'template'     => 'project',
    'num'          => null,
    'content'      => [
        'title'    => 'Project A'
    ],
]);

var_dump($page->title());


