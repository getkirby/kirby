<?php

require '../../vendor/autoload.php';

use Kirby\Toolkit\V;

$input = [
    'title' => 'Some awesome title',
    'email' => 'bastian@getkirby.com'
];

V::input($input, [
    'title' => [
        'min'      => 10,
        'max'      => 30,
        'between'  => [10, 20],
        'required' => true
    ],
    'email' => [
        'email',
        'contains' => 'bastianallgeier.com',
        'required' => true
    ]
]);

