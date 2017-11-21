<?php

require '../vendor/autoload.php';

use Kirby\Html\Attributes;

$attributes = new Attributes;
$attributes->add('href', 'https://getkirby.com');
$attributes->add('rel', 'me');

echo $attributes;
