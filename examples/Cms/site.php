<?php

require '../vendor/autoload.php';

use Kirby\Cms\Site;

$site = new Site([
    'url'       => 'https://getkirby.com',
    'root'      => 'content',
    'content'   => [
        'title' => 'Kirby CMS'
    ],
]);

var_dump($site);

