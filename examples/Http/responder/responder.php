<?php

require '../../vendor/autoload.php';

use Kirby\Http\Responder;

// Strings will be strings
$responder = new Responder;
$response  = $responder->handle('Simple String Response');

var_dump($response);

// Arrays will be converted to a json response
$responder = new Responder;
$response  = $responder->handle(['foo' => 'bar']);

var_dump($response);

// False will trigger a 404
$responder = new Responder;
$response  = $responder->handle(false);

var_dump($response);

// True will send an empty 200
$responder = new Responder;
$response  = $responder->handle(true);

var_dump($response);

// Ints can be used to send the corresponding status code
$responder = new Responder;
$response  = $responder->handle(500);

var_dump($response);
