<?php

$kirby->response()->code(202);
$kirby->response()->header('Cache-Control', 'private');
$kirby->response()->type('text/plain');

echo 'This is a test: ' . uniqid();
