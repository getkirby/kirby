<?php

require '../vendor/autoload.php';

use Kirby\Cms\Page;
use Kirby\Cms\Pages;

$pages = new Pages([
    [
        'id'  => 'projects/project-a',
        'url' => 'https://getkirby.com/projects/project-a',
    ],
    [
        'id'  => 'projects/project-b',
        'url' => 'https://getkirby.com/projects/project-b'
    ],
]);


var_dump($pages->first());
