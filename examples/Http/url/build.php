<?php

require '../../vendor/autoload.php';

use Kirby\Http\Url;

$url = new Url();

echo $url
    ->scheme('http')
    ->username('testuser')
    ->password('weakpassword')
    ->host('getkirby.com')
    ->port('3000')
    ->path('docs/getting-started')
    ->query('?q=awesome')
    ->fragment('#top')
    ->toString();
