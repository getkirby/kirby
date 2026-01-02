<?php

use Kirby\Cms\User;
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

			throw new NotFoundException(
				message: 'The user cannot be found'
			);
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
				throw new InvalidArgumentException(
					message: 'Invalid CSRF token'
				);
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
				throw new InvalidArgumentException(
					message: 'Invalid CSRF token'
				);
			}

			$email    = $this->requestBody('email');
			$long     = $this->requestBody('long');
			$password = $this->requestBody('password');

			if ($password) {
				$result = $auth->authenticate('password', $email, $password, $long);
			} else {
				$mode = match (true) {
					isset($methods['code']) 		  => 'login',
					isset($methods['password-reset']) => 'password-reset',
					default => throw new InvalidArgumentException(
						message: 'Login without password is not enabled'
					)
				};

				$result = $auth->createChallenge($email, $long, $mode);
			}

			if ($result instanceof User) {
				return [
					'code'   => 200,
					'status' => 'ok',
					'user'   => $this->resolve($result)->view('auth')->toArray()
				];
			}

			return [
				'code'      => 200,
				'status'    => 'ok',
				'challenge' => $result->challenge()
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
	[
		'pattern' => 'auth/ping',
		'method'  => 'POST',
		'auth'    => false,
		'action'  => function () {
			// refresh the session timeout
			$this->kirby()->session();
			return true;
		}
	],
];
