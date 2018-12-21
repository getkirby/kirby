<?php

/**
 * System Routes
 */
return [

    [
        'pattern' => 'system',
        'method'  => 'GET',
        'auth'    => false,
        'action'  => function () {
            $system = $this->kirby()->system();

            if ($this->kirby()->user()) {
                return $system;
            } else {
                return [
                    'status' => 'ok',
                    'data'   => $this->resolve($system)->view('login')->toArray(),
                    'type'   => 'model'
                ];
            }
        }
    ],
    [
        'pattern' => 'system/register',
        'method'  => 'POST',
        'action'  => function () {
            return $this->kirby()->system()->register($this->requestBody('license'), $this->requestBody('email'));
        }
    ],
    [
        'pattern' => 'system/install',
        'method'  => 'POST',
        'auth'    => false,
        'action'  => function () {
            $system = $this->kirby()->system();

            if ($system->isOk() === false) {
                throw new Exception('The server is not setup correctly');
            }

            if ($system->isInstalled() === true) {
                throw new Exception('The panel is already installed');
            }

            // create the first user
            $user  = $this->users()->create($this->requestBody());
            $token = $user->login($this->requestBody('password'));

            return [
                'status' => 'ok',
                'token'  => $token,
                'user'   => $this->resolve($user)->view('auth')->toArray()
            ];
        }
    ]

];
