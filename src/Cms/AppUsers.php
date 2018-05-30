<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Form\Field;
use Kirby\Session\Session;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\Str;

trait AppUsers
{
    protected function currentUserFromBasicAuth(string $authorization)
    {
        $credentials = base64_decode(substr($authorization, 6));
        $id          = Str::before($credentials, ':');
        $password    = Str::after($credentials, ':');
        $user        = $this->users()->find($id);

        if ($user->validatePassword($password) === true) {
            $this->loadTranslation($user->language());
            return $user;
        }

        return null;
    }

    protected function currentUserFromUsername(string $username)
    {
        if ($user = $this->users()->find($username)) {
            // Init the user language
            $this->loadTranslation($user->language());

            return $user;
        }

        return null;
    }

    protected function currentUserFromSession($session = null)
    {
        // use passed session options or session object if set
        if (is_array($session)) {
            $session = $this->session($session);
        }

        // try session in header or cookie
        if (is_a($session, Session::class) === false) {
            $session = $this->session(['detect' => true]);
        }

        $id = $session->data()->get('user.id');

        if (is_string($id) !== true) {
            return null;
        }

        if ($user = $this->users()->find($id)) {
            // Init the user language
            $this->loadTranslation($user->language());

            // in case the session needs to be updated, do it now
            // for better performance
            $session->commit();

            return $user;
        }

        return null;
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
     * @param  Session|array $session Session options or session object for getting the current user
     * @return User|null
     */
    public function user(string $id = null, $session = null)
    {
        if ($id !== null) {
            return $this->users()->find($id);
        }

        if (is_a($this->user, User::class) === true) {
            $this->loadTranslation($this->user->language());
            return $this->user;
        }

        if (is_string($this->user) === true) {
            return $this->user = $this->currentUserFromUsername($this->user);
        }

        try {
            $authorization = $this->request()->headers()['Authorization'] ?? '';

            if (Str::startsWith($authorization, 'Basic ') === true) {
                return $this->user = $this->currentUserFromBasicAuth($authorization);
            } else {
                return $this->user = $this->currentUserFromSession($session);
            }
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Returns all users
     *
     * @return Users
     */
    public function users(): Users
    {
        if (is_a($this->users, Users::class) === true) {
            return $this->users;
        }

        return $this->users = Users::load($this->root('accounts'), ['kirby' => $this]);
    }
}
