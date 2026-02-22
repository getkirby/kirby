<?php

namespace Kirby\Auth\Method;

use Kirby\Auth\Auth;
use Kirby\Auth\Method;
use Kirby\Cms\User;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\PermissionException;
use Kirby\Http\Request\Auth\BasicAuth;
use SensitiveParameter;

/**
 * HTTP basic authentication
 *
 * @package   Kirby Auth
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class BasicAuthMethod extends Method
{
	/**
	 * Basic auth authenticates on every request,
	 * so `$long` isn't relevant here
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is missing
	 */
	public function authenticate(
		string $email,
		#[SensitiveParameter]
		string|null $password = null,
		bool $long = false
	): User {
		if ($password === null) {
			throw new InvalidArgumentException(
				message: 'Missing password'
			);
		}

		return $this->auth->validatePassword($email, $password);
	}

	/**
	 * Checks whether the current request attempts
	 * to use HTTP basic authentication
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException if the authorization header is invalid
	 * @throws \Kirby\Exception\PermissionException if basic authentication is not allowed
	 */
	public static function isEnabled(
		Auth $auth,
		array $options = [],
		bool $fail = false
	): bool {
		$kirby   = $auth->kirby();
		$request = $kirby->request();

		if ($kirby->option('api.basicAuth', false) !== true) {
			if ($fail === true) {
				throw new PermissionException(
					message: 'Basic authentication is not activated'
				);
			}

			return false;
		}

		$requestAuth = $options['auth'] ?? $request->auth();

		if ($requestAuth instanceof BasicAuth === false) {
			if ($fail === true) {
				throw new InvalidArgumentException(
					message: 'Invalid authorization header'
				);
			}

			return false;
		}

		// if logging in with password is disabled,
		// basic auth cannot be possible either
		$methods = $auth->methods()->config();

		if (array_key_exists('password', $methods) !== true) {
			if ($fail === true) {
				throw new PermissionException(
					message: 'Login with password is not enabled'
				);
			}

			return false;
		}

		// if any login method requires 2FA,
		// basic auth without 2FA would be a weakness
		if (in_array(true, array_column($methods, '2fa'), true) === true) {
			if ($fail === true) {
				throw new PermissionException(
					message: 'Basic authentication cannot be used with 2FA'
				);
			}

			return false;
		}

		// only allow basic auth when https is enabled or
		// insecure requests permitted
		if (
			$request->ssl() === false &&
			$kirby->option('api.allowInsecure', false) !== true
		) {
			if ($fail === true) {
				throw new PermissionException(
					message: 'Basic authentication is only allowed over HTTPS'
				);
			}

			return false;
		}

		return true;
	}

	/**
	 * Returns the user resolved from the basic auth header
	 */
	public function user(
		BasicAuth|null $auth = null
	): User|null {
		$config = ['auth' => $auth];

		// ensure basic auth method is actually enabled
		// with the provided config
		if (static::isEnabled($this->auth, $config, true) === true) {
			/**
			 * @var \Kirby\Http\Request\Auth\BasicAuth $auth
			 */
			$auth ??= $this->auth->kirby()->request()->auth();

			return $this->authenticate(
				$auth->username(),
				$auth->password(),
			);
		}

		return null;
	}
}
