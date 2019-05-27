<?php

namespace Kirby\Cms;

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
     * @return Kirby\Cms\Auth
     */
    public function auth()
    {
        return $this->auth = $this->auth ?? new Auth($this);
    }

    /**
     * Become any existing user
     *
     * @param string|null $who
     * @return Kirby\Cms\User|null
     */
    public function impersonate(string $who = null)
    {
        return $this->auth()->impersonate($who);
    }

    /**
     * Set the currently active user id
     *
     * @param  Kirby\Cms\User|string $user
     * @return Kirby\Cms\App
     */
    protected function setUser($user = null)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Create your own set of app users
     *
     * @param array $users
     * @return Kirby\Cms\App
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
     * @param  string $id
     * @return Kirby\Cms\User|null
     */
    public function user(string $id = null)
    {
        if ($id !== null) {
            return $this->users()->find($id);
        }

        if (is_string($this->user) === true) {
            return $this->auth()->impersonate($this->user);
        } else {
            return $this->auth()->user();
        }
    }

    /**
     * Returns all users
     *
     * @return Kirby\Cms\Users
     */
    public function users()
    {
        if (is_a($this->users, 'Kirby\Cms\Users') === true) {
            return $this->users;
        }

        return $this->users = Users::load($this->root('accounts'), ['kirby' => $this]);
    }
}
