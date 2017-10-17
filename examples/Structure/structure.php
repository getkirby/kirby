<?php

require '../../vendor/autoload.php';
require '../../extensions/methods.php';

use Kirby\Structure\Collection;

$structure = new Collection([
    [
        'name'  => 'Bastian',
        'email' => 'bastian@getkirby.com'
    ],
    [
        'name'  => 'Fabian',
        'email' => 'fabian@getkirby.com'
    ],
    [
        'name'  => 'Lukas',
        'email' => 'lukas@getkirby.com'
    ],
    [
        'name'  => 'Nico',
        'email' => 'nico@getkirby.com'
    ],
    [
        'name'  => 'Nils',
        'email' => 'nils@getkirby.com'
    ],
    [
        'name'  => 'Sonja',
        'email' => 'sonja@getkirby.com'
    ]
]);

var_dump($structure->toArray());
