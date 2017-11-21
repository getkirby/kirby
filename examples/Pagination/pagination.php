<?php

require '../../vendor/autoload.php';

use Kirby\Pagination\Pagination;

$pagination = new Pagination();
$pagination->page(2);
$pagination->total(43);
$pagination->limit(20);

var_dump($pagination->toArray());
