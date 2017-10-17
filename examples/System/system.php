<?php

require '../../vendor/autoload.php';

use Kirby\Cms\System;
use Kirby\Cms\App;

$app = new App([
    'root' => [
        'thumbs'   => __DIR__,
        'accounts' => __DIR__,
        'content'  => __DIR__
    ]
]);

$system = new System($app);

var_dump($system->status());
