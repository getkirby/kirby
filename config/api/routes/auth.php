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
        'pattern' => 'auth/code',
        'method'  => 'POST',
        'auth'    => false,
        'action'  => function () {
            $auth = $this->kirby()->auth();

            // csrf token check
            if ($auth->type() === 'session' && $auth->csrf() === false) {
                throw new InvalidArgumentException('Invalid CSRF token');
            }

            $user = $auth->verifyChallenge($this->requestBody('code'));

            return [
                'code'   => 200,
                'status' => 'ok',
                'user'   => $this->resolve($user)->view('auth')->toArray()
            ];
        }
    ],
    [
        'pattern' => 'auth/login',
        'method'  => 'POST',
        'auth'    => false,
        'action'  => function () {
            $auth    = $this->kirby()->auth();
            $methods = $this->kirby()->system()->loginMethods();

            // csrf token check
            if ($auth->type() === 'session' && $auth->csrf() === false) {
                throw new InvalidArgumentException('Invalid CSRF token');
            }

            $email    = $this->requestBody('email');
            $long     = $this->requestBody('long');
            $password = $this->requestBody('password');

            if ($password) {
                if (isset($methods['password']) !== true) {
                    throw new InvalidArgumentException('Login with password is not enabled');
                }

                if (
                    isset($methods['password']['2fa']) === true &&
                    $methods['password']['2fa'] === true
                ) {
                    $status = $auth->login2fa($email, $password, $long);
                } else {
                    $user = $auth->login($email, $password, $long);
                }
            } else {
                if (isset($methods['code']) === true) {
                    $mode = 'login';
                } elseif (isset($methods['password-reset']) === true) {
                    $mode = 'password-reset';
                } else {
                    throw new InvalidArgumentException('Login without password is not enabled');
                }

                $status = $auth->createChallenge($email, $long, $mode);
            }

            if (isset($user)) {
                return [
                    'code'   => 200,
                    'status' => 'ok',
                    'user'   => $this->resolve($user)->view('auth')->toArray()
                ];
            } else {
                return [
                    'code'      => 200,
                    'status'    => 'ok',
                    'challenge' => $status->challenge()
                ];
            }
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
