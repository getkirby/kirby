<?php

require '../vendor/autoload.php';

use Kirby\Cms\Page;

$page = new Page([
    'id'   => 'projects/project-a',
    'url'  => 'https://getkirby.com/projects/project-a',
    'root' => 'content/1-projects/1-project-a'
]);

var_dump($page);
var_dump($page->files());
