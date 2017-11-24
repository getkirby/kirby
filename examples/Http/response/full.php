<?php

require '../../vendor/autoload.php';

use Kirby\Http\Response;

$response = new Response;
$response->type('text/html');
$response->charset('UTF-8');
$response->code(200);
$response->body('example response');

echo $response->send();
