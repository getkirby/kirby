<?php

require '../vendor/autoload.php';

use Kirby\Cms\Page;

$page = new Page([
    'id'   => 'projects',
    'url'  => 'https://getkirby.com/projects',
    'root' => 'content/1-projects'
]);

var_dump($page);
