<?php

require '../vendor/autoload.php';

use Kirby\Html\Element;

$div = new Element('div');
$div->addClass('container');

echo $div->begin();
echo 'Some content in between';
echo $div->end();
