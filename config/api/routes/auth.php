<?php

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

            throw new Exception('The user cannot be found');
        }
    ],
    [
        'pattern' => 'auth/login',
        'method'  => 'POST',
        'action'  => function () {

            // assemble session options
            $options = [
                'createMode' => 'cookie',
                'long'       => $this->requestQuery('long') === 'true'
            ];

            // log in to the session
            $this->user(null, $options)->loginPasswordless();

            return [
                'status' => 'ok',
                'user'   => $this->resolve($this->user())->view('auth')->toArray()
            ];

        }
    ],
    [
        'pattern' => 'auth/token',
        'method'  => 'POST',
        'action'  => function () {

            // assemble session options
            $options = [
                'createMode' => 'header',
                'long'       => $this->requestQuery('long') === 'true'
            ];

            // log in to the session
            $this->user(null, $options)->loginPasswordless();

            return [
                'status' => 'ok',
                // TODO: Remove the following line once the token is transmitted on the
                //       top-level of the response anyway
                'token'  => $this->session()->token(),
                'user'   => $this->resolve($this->user())->view('auth')->toArray()
            ];

        }
    ],
    [
        'pattern' => 'auth/logout',
        'method'  => 'POST',
        'action'  => function () {

            // verify that we are logged in via session
            $authorization = $this->requestHeaders('Authorization', '');
            if (Str::startsWith($authorization, 'Basic ')) {
                throw new Exception('Cannot log out of HTTP Basic authentication');
            }

            // logout of the current, detected session
            $this->user()->logout();

            return [
                'status' => 'ok'
            ];

        }
    ],
];
