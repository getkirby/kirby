<?php

require '../../vendor/autoload.php';

use Kirby\Http\Response\Json;

$json = new Json([
    'status'  => 'ok',
    'message' => 'nice'
]);

$json->code(202);

echo $json->send();
