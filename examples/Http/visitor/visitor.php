<?php

require '../../vendor/autoload.php';

use Kirby\Http\Visitor;

$visitor = new Visitor;

var_dump($visitor->ip());
var_dump($visitor->userAgent());
var_dump($visitor->acceptedLanguage()->code());
var_dump($visitor->acceptedMimeType()->value());
var_dump($visitor->accepts('text/html'));
