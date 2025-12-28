<?php

namespace Kirby\Auth;

use Kirby\Auth\Exception\InvalidAuthHeaderException;
use Kirby\Auth\Exception\UserNotFoundException;
use Kirby\Cms\App;
use Kirby\Cms\Auth;
use Kirby\Cms\User as CmsUser;
use Kirby\Exception\PermissionException;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Session\Session;
use Throwable;

class User
{
	protected CmsUser|false|null $current = false;
	protected CmsUser|null $impersonate = null;
	protected Throwable|null $exception = null;

	public function __construct(
		protected Auth $auth,
		protected App $kirby,
	) {
	}

	/**
	 * Returns a user resolved from basic auth
	 *
	 * @throws \Kirby\Auth\Exception\InvalidAuthHeaderException
	 * @throws \Kirby\Exception\PermissionException
	 */
	public function currentFromBasicAuth(
		BasicAuth|null $auth = null
	): CmsUser|null {
		if ($this->kirby->option('api.basicAuth', false) !== true) {
			throw new PermissionException(
				message: 'Basic authentication is not activated'
			);
		}

		// if logging in with password is disabled,
		// basic auth cannot be possible either
		$loginMethods = $this->kirby->system()->loginMethods();

		if (isset($loginMethods['password']) !== true) {
			throw new PermissionException(
				'Login with password is not enabled'
			);
		}

		// if any login method requires 2FA,
		// basic auth without 2FA would be a weakness
		foreach ($loginMethods as $method) {
			if (isset($method['2fa']) === true && $method['2fa'] === true) {
				throw new PermissionException(
					'Basic authentication cannot be used with 2FA'
				);
			}
		}

		$request = $this->kirby->request();
		$auth  ??= $request->auth();

		if (!$auth || $auth->type() !== 'basic') {
			throw new InvalidAuthHeaderException();
		}

		// only allow basic auth when https is enabled or
		// insecure requests permitted
		if (
			$request->ssl() === false &&
			$this->kirby->option('api.allowInsecure', false) !== true
		) {
			throw new PermissionException(
				message: 'Basic authentication is only allowed over HTTPS'
			);
		}

		/**
		 * @var \Kirby\Http\Request\Auth\BasicAuth $auth
		 */
		return $this->auth->validatePassword(
			$auth->username(),
			$auth->password()
		);
	}

	public function currentFromImpersonation(): CmsUser|null
	{
		return $this->impersonate;
	}

	public function currentFromSession(
		Session|array|null $session = null
	): CmsUser|null {
		if (is_array($session) === true) {
			$session = $this->kirby->session($session);
		} elseif ($session instanceof Session === false) {
			$session = $this->kirby->session(['detect' => true]);
		}

		$id = $session->data()->get('kirby.userId');

		// if no user is logged in, return immediately
		if (is_string($id) !== true) {
			return null;
		}

		// a user is logged in, ensure it exists
		$user = $this->kirby->users()->find($id);

		if ($user === null) {
			return null;
		}

		if ($passwordTimestamp = $user->passwordTimestamp()) {
			$loginTimestamp = $session->data()->get('kirby.loginTimestamp');

			if (is_int($loginTimestamp) !== true) {
				// session that was created before Kirby
				// 3.5.8.3, 3.6.6.3, 3.7.5.2, 3.8.4.1 or 3.9.6
				// or when the user didn't have a password set
				$user->logout();
				return null;
			}

			// invalidate the session if the password
			// changed since the login
			if ($loginTimestamp < $passwordTimestamp) {
				$user->logout();
				return null;
			}
		}

		// in case the session needs to be updated,
		// do it now for better performance
		$session->commit();

		return $user;
	}

	public function flush(): void
	{
		$this->current     = null;
		$this->exception   = null;
		$this->impersonate = null;
	}

	/**
	 * Become any existing user or disable the current user
	 *
	 * @throws \Kirby\Auth\Exception\UserNotFoundException
	 */
	public function impersonate(string|null $who = null): CmsUser|null
	{
		if ($who === null) {
			return $this->impersonate = null;
		}

		if ($who === 'kirby') {
			return $this->impersonate = new CmsUser([
				'email' => 'kirby@getkirby.com',
				'id'    => 'kirby',
				'role'  => 'admin',
			]);
		}

		if ($who === 'nobody') {
			return $this->impersonate = new CmsUser([
				'email' => 'nobody@getkirby.com',
				'id'    => 'nobody',
				'role'  => 'nobody',
			]);
		}

		if ($user = $this->kirby->users()->find($who)) {
			return $this->impersonate = $user;
		}

		throw new UserNotFoundException(name: $who);
	}

	public function isImpersonated(): bool
	{
		return $this->impersonate !== null;
	}

	public function set(CmsUser $user): void
	{
		// stop impersonating
		$this->impersonate = null;
		$this->current     = $user;
	}

	public function user(
		Session|array|null $session = null,
		bool $allowImpersonation = true
	): CmsUser|null {
		if ($allowImpersonation === true && $this->isImpersonated()) {
			return $this->impersonate;
		}

		// return from cache
		if ($this->current === null) {
			// throw the same Exception again if one was captured before
			if ($this->exception !== null) {
				throw $this->exception;
			}

			return null;
		}

		if ($this->current !== false) {
			return $this->current;
		}

		try {
			$type = $this->auth->type($allowImpersonation);
			return $this->current = match ($type) {
				'basic' => $this->currentFromBasicAuth(),
				default => $this->currentFromSession($session)
			};

		} catch (Throwable $e) {
			// capture the Exception for future calls
			$this->exception = $e;
			$this->current = null;

			throw $e;
		}
	}
}
