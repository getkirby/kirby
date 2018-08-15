<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Exception\NotFoundException;
use Kirby\Form\Field;
use Kirby\Session\Session;
use Kirby\Toolkit\Dir;
use Kirby\Toolkit\I18n;
use Kirby\Toolkit\Str;
use Throwable;

trait AppUsers
{
    protected function currentUserFromBasicAuth(string $authorization)
    {
        $credentials = base64_decode(substr($authorization, 6));
        $id          = Str::before($credentials, ':');
        $password    = Str::after($credentials, ':');
        $user        = $this->users()->find($id);

        if ($user->validatePassword($password) === true) {
            I18n::$locale = $user->language();
            return $user;
        }

        return null;
    }

    protected function currentUserFromUsername(string $username)
    {
        if ($user = $this->users()->find($username)) {
            // Init the user language
            I18n::$locale = $user->language();
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
        if (is_a($session, 'Kirby\Session\Session') === false) {
            $session = $this->session(['detect' => true]);
        }

        $id = $session->data()->get('user.id');

        if (is_string($id) !== true) {
            return null;
        }

        if ($user = $this->users()->find($id)) {
            I18n::$locale = $user->language();

            // in case the session needs to be updated, do it now
            // for better performance
            $session->commit();

            return $user;
        }

        return null;
    }

    /**
     * Become any existing user
     *
     * @param string|null $who
     * @return self
     */
    public function impersonate(string $who = null)
    {
        if ($who === null) {
            $this->user = null;
            return $this;
        }

        if ($who === 'kirby') {
            $this->user = new User([
                'email' => 'kirby@getkirby.com',
                'role'  => 'admin'
            ]);

            return $this;
        }

        if ($user = $this->users()->find($who)) {
            $this->user = $user;
            return $this;
        }

        throw new NotFoundException('The user "' . $who . '" cannot be found');
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

        if (is_a($this->user, 'Kirby\Cms\User') === true) {
            I18n::$locale = $this->user->language();
            return $this->user;
        }

        if (is_string($this->user) === true) {
            return $this->user = $this->currentUserFromUsername($this->user);
        }

        try {
            $basicAuth     = $this->options['api']['basicAuth'] ?? false;
            $authorization = $this->request()->headers()['Authorization'] ?? '';

            if ($basicAuth === true && Str::startsWith($authorization, 'Basic ') === true) {
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
        if (is_a($this->users, 'Kirby\Cms\Users') === true) {
            return $this->users;
        }

        return $this->users = Users::load($this->root('accounts'), ['kirby' => $this]);
    }
}
