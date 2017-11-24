<?php

require '../vendor/autoload.php';

use Kirby\Html\ClassList;

$classes = new ClassList;
$classes->add('awesome', 'nice', 'fantastic');
$classes->add('test');
$classes->remove('test');

echo $classes;
