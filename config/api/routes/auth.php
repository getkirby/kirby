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
            if ($user = $this->user()) {
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

            // logout previous users
            if ($user = $this->user()) {
                $user->logout();
            }

            // session options
            $options = [
                'createMode' => 'cookie',
                'long'       => $this->requestBody('long') === true
            ];

            // validate the user and log in to the session
            if ($user = $this->user($this->requestBody('email'))) {
                if ($user->login($this->requestBody('password'), $options) === true) {
                    return [
                        'code'   => 200,
                        'status' => 'ok',
                        'user'   => $this->resolve($this->user())->view('auth')->toArray()
                    ];
                }
            }

            throw new InvalidArgumentException('Invalid email or password');

        }
    ],
    [
        'pattern' => 'auth/logout',
        'method'  => 'POST',
        'auth'    => false,
        'action'  => function () {

            // verify that we are logged in via session
            $authorization = $this->requestHeaders('Authorization', '');

            if (Str::startsWith($authorization, 'Basic ')) {
                throw new Exception('Cannot log out of HTTP Basic authentication');
            }

            // logout of the current, detected session
            if ($user = $this->user()) {
                $user->logout();
            }

            return [
                'code'   => 200,
                'status' => 'ok'
            ];

        }
    ],
];
