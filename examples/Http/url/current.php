<?php

require '../../vendor/autoload.php';

use Kirby\Http\Url;

$url = Url::current();

var_dump($url);
