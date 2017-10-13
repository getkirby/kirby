<?php

require '../vendor/autoload.php';

use Kirby\Cms\Blueprint;

$blueprint = new Blueprint(__DIR__ . '/blueprints', 'blog');
var_dump($blueprint->toArray());
