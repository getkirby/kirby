<?php

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
            $user  = $this->user($id);
            $token = $user->login($password);

            return [
                'status' => 'ok',
                'token'  => $token,
                'user'   => $this->resolve($user)->view('auth')->toArray()
            ];

        }
    ],
    [
        'pattern' => 'auth/user',
        'method'  => 'GET',
        'action'  => function () {
            return $this->resolve($this->user())->view('auth');
        }
    ]
];
