<?php

require '../../kirby/bootstrap.php';

$kirby = new Kirby([
    'options' => [
        'debug' => true,
        'email' => [
            'transport' => [
                'type'      => 'smtp',
                'host'      => 'smtp.mailtrap.io',
                'port'      => 465,
                'auth'      => 'LOGIN',
                'username'  => 'b3921026d97a2f',
                'password'  => 'f4175395c9ffc2',
                'ssl'       => false
            ],
            'presets' => [
                'welcome' => [
                    'from'     => 'no-reply@super.co',
                    'subject'  => 'Welcome to our app',
                    'cc'       => 'marketing@super.co',
                    'template' => 'welcome'
                ]
            ]
        ]
    ],
    'roots' => [
        'index' => __DIR__
    ]
]);

try {
    $status = $kirby->email('welcome', [
        'to'   => 'peter@lustig.de',
        'data' => [
            'name' => 'Peter'
        ]
    ]);

    die('The email has been sent');
} catch (Exception $e) {
    var_dump($e);
}


