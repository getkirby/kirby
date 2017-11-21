<?php

require '../../vendor/autoload.php';

use Kirby\Data\Handler\Json;

// encoding
var_dump($json = Json::encode([
    'name'     => 'Homer Simpson',
    'email'    => 'homer@simpson.com',
    'children' => [
        'Lisa',
        'Bart',
        'Maggie'
    ]
]));

// decoding
var_dump(Json::decode($json));
