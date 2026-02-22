<?php

namespace Kirby\Auth;

use Kirby\Cms\App;
use Kirby\Cms\User as CmsUser;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Session\Session;
use Throwable;

/**
 * Handler for the authenticated or impersonated user
 *
 * @package   Kirby Auth
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 * @since     6.0.0
 */
class User
{
	/**
	 * Instance of the currently logged in user or
	 * `false` if the user was not yet determined
	 */
	protected CmsUser|false|null $current = false;

	/**
	 * Currently impersonated user
	 */
	protected CmsUser|null $impersonate = null;

	/**
	 * Exception that was thrown while
	 * determining the current user
	 */
	protected Throwable|null $exception = null;

	public function __construct(
		protected Auth $auth,
		protected App $kirby,
	) {
	}

	public function flush(): void
	{
		$this->current     = null;
		$this->exception   = null;
		$this->impersonate = null;
	}

	/**
	 * Returns the logged in user by checking for a
	 * basic authentication header with valid credentials
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException if the authorization header is invalid
	 * @throws \Kirby\Exception\PermissionException if basic authentication is not allowed
	 */
	public function fromBasicAuth(
		BasicAuth|null $auth = null
	): CmsUser|null {
		/**
		 * @var \Kirby\Auth\Method\BasicAuthMethod
		 */
		$basic = $this->auth->methods()->get('basic-auth');
		return $basic->user($auth);
	}

	/**
	 * Returns the currently impersonated user
	 */
	public function fromImpersonation(): CmsUser|null
	{
		return $this->impersonate;
	}

	/**
	 * Returns the logged in user by checking the current session
	 * and finding a valid valid user id in there
	 */
	public function fromSession(
		Session|array|null $session = null
	): CmsUser|null {
		$session = $this->kirby->auth()->session($session);
		$id      = $session->data()->get('kirby.userId');

		// if no user is logged in, return immediately
		if (is_string($id) !== true) {
			return null;
		}

		// a user is logged in, ensure it exists
		$user = $this->kirby->user($id);

		if ($user === null) {
			return null;
		}

		if ($password = $user->passwordTimestamp()) {
			$login = $session->data()->get('kirby.loginTimestamp');

			// invalidate the session if the password
			// changed since the login
			if ($login < $password) {
				$user->logout();
				return null;
			}
		}

		// in case the session needs to be updated,
		// do it now for better performance
		$session->commit();

		return $user;
	}

	/**
	 * Returns the currently logged in user
	 *
	 * @param bool $allowImpersonation If set to false, only the actually
	 *                                 logged in user will be returned
	 *
	 * @throws \Throwable If an authentication error occurred
	 */
	public function get(
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
				'basic' => $this->fromBasicAuth(),
				default => $this->fromSession($session)
			};
		} catch (Throwable $e) {
			// capture exception for future calls
			$this->exception = $e;
			$this->current = null;

			throw $e;
		}
	}

	/**
	 * Impersonates a user
	 *
	 * @throws \Kirby\Exception\NotFoundException if the given user cannot be found
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

		throw new NotFoundException(
			message: 'The user "' . $who . '" cannot be found'
		);
	}

	/**
	 * Returns whether the user is currently impersonated
	 */
	public function isImpersonated(): bool
	{
		return $this->impersonate !== null;
	}

	/**
	 * Sets a user object as the current user in the cache
	 * @internal
	 */
	public function set(CmsUser|null $user): void
	{
		// stop impersonating
		$this->impersonate = null;
		$this->current     = $user;
	}
}
