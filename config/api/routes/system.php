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
            return $this->kirby()->system();
        }
    ],
    [
        'pattern' => 'system/register',
        'method'  => 'POST',
        'action'  => function () {
            return $this->upload(function ($source) {

                if (F::mime($source) !== 'text/x-php') {
                    throw new Exception('Invalid license file');
                }

                if (F::copy($source, $this->kirby()->root('config') . '/license.php') !== true) {
                    throw new Exception('The license file could not be uploaded');
                }

                return true;
            });
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
