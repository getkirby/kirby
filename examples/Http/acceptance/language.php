<?php

require '../../vendor/autoload.php';

use Kirby\Http\Acceptance\Language;

$lang = 'en,de;q=0.8,en-US;q=0.6';

$acceptance = new Language($lang);

var_dump($acceptance->info());
var_dump($acceptance->code());
var_dump($acceptance->locale());
var_dump($acceptance->region());
var_dump($acceptance->quality());
var_dump($acceptance->items());
var_dump($acceptance->is('de'));
var_dump($acceptance->has('de'));
