<?php

require '../vendor/autoload.php';

use Kirby\Html\Attribute;

$attr = new Attribute('href', 'https://getkirby.com');

echo $attr->toHtml();
