<?php

namespace Kirby\Cms;

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
     * @return Auth
     */
    public function auth()
    {
        return $this->auth = $this->auth ?? new Auth($this);
    }

    /**
     * Become any existing user
     *
     * @param string|null $who
     * @return self
     */
    public function impersonate(string $who = null)
    {
        return $this->auth()->impersonate($who);
    }

    /**
     * Set the currently active user id
     *
     * @param  User|string $user
     * @return self
     */
    protected function setUser($user = null): self
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Create your own set of app users
     *
     * @param array $users
     * @return self
     */
    protected function setUsers(array $users = null): self
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
     * @param  string        $id
     * @param  \Kirby\Session\Session|array $session Session options or session object for getting the current user
     * @return User|null
     */
    public function user(string $id = null, $session = null)
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
     * @return Users
     */
    public function users(): Users
    {
        if (is_a($this->users, 'Kirby\Cms\Users') === true) {
            return $this->users;
        }

        return $this->users = Users::load($this->root('accounts'), ['kirby' => $this]);
    }
}
