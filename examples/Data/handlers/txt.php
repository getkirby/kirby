<?php

require '../../vendor/autoload.php';

use Kirby\Data\Handler\Txt;

// encoding
var_dump($txt = Txt::encode([
    'name'     => 'Homer Simpson',
    'email'    => 'homer@simpson.com',
    'children' => [
        'Lisa',
        'Bart',
        'Maggie'
    ]
]));

// decoding
var_dump(Txt::decode($txt));
