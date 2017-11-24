<?php

require '../../vendor/autoload.php';

use Kirby\Http\Url;

$url = new Url('https://testuser:weakpassword@getkirby.com:3000/docs/getting-started/?q=awesome#top');

echo $url
    ->scheme('http')
    ->username(false)
    ->password(false)
    ->port(false)
    ->query(false)
    ->toString();
