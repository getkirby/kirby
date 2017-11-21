<?php

require '../vendor/autoload.php';

use Kirby\Content\Content;

// Txt
$content = new Content('data/data.txt');

var_dump($content->get('name'));
var_dump($content->get('email'));

// Json
$content = new Content('data/data.json');

var_dump($content->get('name'));
var_dump($content->get('email'));
