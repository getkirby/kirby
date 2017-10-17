<?php

require '../vendor/autoload.php';

use Kirby\Cms\Schema;
use Kirby\Cms\Blueprint;


$blueprint = new Blueprint(__DIR__ . '/blueprints', 'default');
$schema    = new Schema(null, $blueprint->toArray(), []);

$schema->validate([
    'intro' => 'Super',
    'text'  => 'Something something'
]);



