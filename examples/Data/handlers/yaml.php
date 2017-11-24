<?php

require '../../vendor/autoload.php';

use Kirby\Data\Handler\Yaml;

// encoding
var_dump($yaml = Yaml::encode([
    'name'     => 'Homer Simpson',
    'email'    => 'homer@simpson.com',
    'children' => [
        'Lisa',
        'Bart',
        'Maggie'
    ]
]));

// decoding
var_dump(Yaml::decode($yaml));
