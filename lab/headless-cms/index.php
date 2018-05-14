<?php

require '../../kirby/bootstrap.php';

$kirby = new Kirby([
    'roots' => [
        'index'    => __DIR__,
        'sessions' => __DIR__ . '/sessions',
    ],
    'users' => [
        [
            'email' => 'bastian@getkirby.com',
            'role'  => 'admin'
        ]
    ],
    'user' => 'bastian@getkirby.com'
]);

echo $kirby->call('api/' . $kirby->path());
