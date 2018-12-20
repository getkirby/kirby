<?php

use Kirby\Exception\NotFoundException;
use Kirby\Exception\InvalidArgumentException;

/**
 * Authentication
 */
return [
    [
        'pattern' => 'auth',
        'method'  => 'GET',
        'action'  => function () {
            if ($user = $this->kirby()->auth()->user()) {
                return $this->resolve($user)->view('auth');
            }

            throw new NotFoundException('The user cannot be found');
        }
    ],
    [
        'pattern' => 'auth/login',
        'method'  => 'POST',
        'auth'    => false,
        'action'  => function () {
            $email    = $this->requestBody('email');
            $long     = $this->requestBody('long');
            $password = $this->requestBody('password');

            if ($user = $this->kirby()->auth()->login($email, $password, $long)) {
                return [
                    'code'   => 200,
                    'status' => 'ok',
                    'user'   => $this->resolve($user)->view('auth')->toArray()
                ];
            }

            throw new InvalidArgumentException('Invalid email or password');
        }
    ],
    [
        'pattern' => 'auth/logout',
        'method'  => 'POST',
        'auth'    => false,
        'action'  => function () {
            $this->kirby()->auth()->logout();
            return true;
        }
    ],
];
