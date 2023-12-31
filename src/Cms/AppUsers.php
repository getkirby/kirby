<?php

namespace Kirby\Cms;

use Closure;
use Throwable;

/**
 * AppUsers
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
trait AppUsers
{
	protected Auth|null $auth = null;
	protected User|string|null $user = null;
	protected Users|null $users = null;

	/**
	 * Returns the Authentication layer class
	 * @internal
	 */
	public function auth(): Auth
	{
		return $this->auth ??= new Auth($this);
	}

	/**
	 * Become any existing user or disable the current user
	 *
	 * @param string|null $who User ID or email address,
	 *                         `null` to use the actual user again,
	 *                         `'kirby'` for a virtual admin user or
	 *                         `'nobody'` to disable the actual user
	 * @param Closure|null $callback Optional action function that will be run with
	 *                               the permissions of the impersonated user; the
	 *                               impersonation will be reset afterwards
	 * @return mixed If called without callback: User that was impersonated;
	 *               if called with callback: Return value from the callback
	 * @throws \Throwable
	 */
	public function impersonate(
		string|null $who = null,
		Closure|null $callback = null
	): mixed {
		$auth = $this->auth();

		$userBefore = $auth->currentUserFromImpersonation();
		$userAfter  = $auth->impersonate($who);

		if ($callback === null) {
			return $userAfter;
		}

		try {
			return $callback($userAfter);
		} catch (Throwable $e) {
			throw $e;
		} finally {
			// ensure that the impersonation is *always* reset
			// to the original value, even if an error occurred
			$auth->impersonate($userBefore?->id());
		}
	}

	/**
	 * Set the currently active user id
	 *
	 * @return $this
	 */
	protected function setUser(User|string $user = null): static
	{
		$this->user = $user;
		return $this;
	}

	/**
	 * Create your own set of app users
	 *
	 * @return $this
	 */
	protected function setUsers(array $users = null): static
	{
		if ($users !== null) {
			$this->users = Users::factory($users);
		}

		return $this;
	}

	/**
	 * Returns a specific user by id
	 * or the current user if no id is given
	 *
	 * @param bool $allowImpersonation If set to false, only the actually
	 *                                 logged in user will be returned
	 *                                 (when `$id` is passed as `null`)
	 */
	public function user(
		string|null $id = null,
		bool $allowImpersonation = true
	): User|null {
		if ($id !== null) {
			return $this->users()->find($id);
		}

		if ($allowImpersonation === true && is_string($this->user) === true) {
			return $this->auth()->impersonate($this->user);
		}

		try {
			return $this->auth()->user(null, $allowImpersonation);
		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * Returns all users
	 */
	public function users(): Users
	{
		return $this->users ??= Users::load(
			$this->root('accounts'),
		);
	}
}
