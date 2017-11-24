<?php

require '../../vendor/autoload.php';

use Kirby\Data\Handler\PHP;

// encoding
var_dump($string = PHP::encode([
    'name'     => 'Homer Simpson',
    'email'    => 'homer@simpson.com',
    'children' => [
        'Lisa',
        'Bart',
        'Maggie'
    ]
]));

// decoding
var_dump(PHP::decode($string));
