<?php

require '../../vendor/autoload.php';

use Kirby\Http\Acceptance\MimeType;

$mimeType = 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8';

$acceptance = new MimeType($mimeType);

var_dump($acceptance->info());
var_dump($acceptance->value());
var_dump($acceptance->quality());
var_dump($acceptance->items());
var_dump($acceptance->is('image/jpeg'));
var_dump($acceptance->has('image/jpeg'));
var_dump($acceptance->toArray());
