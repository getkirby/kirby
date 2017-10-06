<?php

require '../vendor/autoload.php';

use Kirby\Cms\Site;

$site = new Site([
    'root' => 'content',
    'url'  => 'https://getkirby.com'
]);

var_dump($site->files());
