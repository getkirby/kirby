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
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
trait AppUsers
{
    /**
     * Cache for the auth auth layer
     *
     * @var Auth
     */
    protected $auth;

    /**
     * Returns the Authentication layer class
     *
     * @internal
     * @return \Kirby\Cms\Auth
     */
    public function auth()
    {
        return $this->auth = $this->auth ?? new Auth($this);
    }

    /**
     * Become any existing user
     *
     * @param string|null $who User ID or email address
     * @param Closure|null $callback Optional action function that will be run with
     *                               the permissions of the impersonated user; the
     *                               impersonation will be reset afterwards
     * @return mixed If called without callback: User that was impersonated;
     *               if called with callback: Return value from the callback
     * @throws \Throwable
     */
    public function impersonate(?string $who = null, ?Closure $callback = null)
    {
        $auth = $this->auth();

        $userBefore = $auth->currentUserFromImpersonation();
        $userAfter  = $auth->impersonate($who);

        if ($callback === null) {
            return $userAfter;
        }

        try {
            // bind the App object to the callback
            return $callback->call($this, $userAfter);
        } catch (Throwable $e) {
            throw $e;
        } finally {
            // ensure that the impersonation is *always* reset
            // to the original value, even if an error occurred
            $auth->impersonate($userBefore !== null ? $userBefore->id() : null);
        }
    }

    /**
     * Set the currently active user id
     *
     * @param \Kirby\Cms\User|string $user
     * @return \Kirby\Cms\App
     */
    protected function setUser($user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Create your own set of app users
     *
     * @param array|null $users
     * @return \Kirby\Cms\App
     */
    protected function setUsers(array $users = null)
    {
        if ($users !== null) {
            $this->users = Users::factory($users, [
                'kirby' => $this
            ]);
        }

        return $this;
    }

    /**
     * Returns a specific user by id
     * or the current user if no id is given
     *
     * @param string|null $id
     * @param bool $allowImpersonation If set to false, only the actually
     *                                 logged in user will be returned
     *                                 (when `$id` is passed as `null`)
     * @return \Kirby\Cms\User|null
     */
    public function user(?string $id = null, bool $allowImpersonation = true)
    {
        if ($id !== null) {
            return $this->users()->find($id);
        }

        if ($allowImpersonation === true && is_string($this->user) === true) {
            return $this->auth()->impersonate($this->user);
        } else {
            try {
                return $this->auth()->user(null, $allowImpersonation);
            } catch (Throwable $e) {
                return null;
            }
        }
    }

    /**
     * Returns all users
     *
     * @return \Kirby\Cms\Users
     */
    public function users()
    {
        if (is_a($this->users, 'Kirby\Cms\Users') === true) {
            return $this->users;
        }

        return $this->users = Users::load($this->root('accounts'), ['kirby' => $this]);
    }
}
