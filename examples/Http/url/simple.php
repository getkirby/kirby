<?php

require '../../vendor/autoload.php';

use Kirby\Http\Url;

$url = new Url('http://testuser:weakpassword@getkirby.com:3000/docs/getting-started/?q=awesome#top');

var_dump($url->scheme());
var_dump($url->username());
var_dump($url->password());
var_dump($url->host());
var_dump($url->port());
var_dump($url->path());
var_dump($url->query());
var_dump($url->fragment());
var_dump($url->base());
var_dump($url->toArray());

echo $url;
