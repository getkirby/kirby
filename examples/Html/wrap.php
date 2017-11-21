<?php

require '../vendor/autoload.php';

use Kirby\Html\Element;
use Kirby\Html\Element\A;

// simple
$link = new A('https://getkirby.com', 'Kirby');
$div  = $link->wrap('div');

echo $div;

// with custom element
$link = new A('https://getkirby.com', 'Kirby');
$div  = $link->wrap(new Element('div', [
    'class' => 'container'
]));

echo $div;

