<?php

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;

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
            $auth = $this->kirby()->auth();

            // csrf token check
            if ($auth->type() === 'session' && $auth->csrf() === false) {
                throw new InvalidArgumentException('Invalid CSRF token');
            }

            $email    = $this->requestBody('email');
            $long     = $this->requestBody('long');
            $password = $this->requestBody('password');

            $user = $this->kirby()->auth()->login($email, $password, $long);

            return [
                'code'   => 200,
                'status' => 'ok',
                'user'   => $this->resolve($user)->view('auth')->toArray()
            ];
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
