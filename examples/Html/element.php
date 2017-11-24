<?php

require '../vendor/autoload.php';

use Kirby\Html\Element;

$a = new Element('a');
$a->addClass('link');
$a->addClass('btn');
$a->attr('href', 'https://getkirby.com');
$a->attr('rel', 'me');
$a->text('Kirby');

echo $a;
