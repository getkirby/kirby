<?php

use Firebase\JWT\JWT;

/**
 * Authentication
 */
return [
    [
        'pattern' => 'auth',
        'auth'    => false,
        'method'  => 'POST',
        'action'  => function () {

            $email    = $this->requestBody('email');
            $password = $this->requestBody('password');
            $id       = sha1($email);

            if (empty($email) === true) {
                throw new Exception('Missing email');
            }

            if (empty($password) === true) {
                throw new Exception('Missing password');
            }

            // try to find the user by the sha1 id
            $user = $this->user($id);

            if ($user->validatePassword($password) !== true) {
                throw new Exception('Invalid email or password');
            }

            // TODO: get the token and expiration from the config
            $key        = 'kirby';
            $expiration = 3600 * 24 * 7;

            // create a json web token
            $token = [
                'iss' => $this->kirby()->url(),
                'aud' => $this->kirby()->url(),
                'iat' => $time = time(),
                'nbf' => $time,
                'exp' => $time + $expiration,
                'uid' => $user->id(),
            ];

            $token = JWT::encode($token, $key);

            return [
                'status' => 'ok',
                'token'  => $token,
                'user'   => $this->resolve($user)->select('id, email, language, name')->toArray()
            ];

        }
    ],
    [
        'pattern' => 'auth/user',
        'method'  => 'GET',
        'action'  => function () {
            return $this->user();
        }
    ]
];
