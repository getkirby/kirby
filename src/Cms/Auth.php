<?php

namespace Kirby\Cms;

use Kirby\Exception\PermissionException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Request\Auth\BasicAuth;
use Throwable;

/**
 * Authentication layer
 */
class Auth
{

    protected $impersonate;
    protected $kirby;
    protected $user;

    /**
     * @param App $kirby
     */
    public function __construct(App $kirby)
    {
        $this->kirby = $kirby;
    }

    /**
     * Returns the csrf token if it exists and if it is valid
     *
     * @return string|false
     */
    public function csrf()
    {
        // get the csrf from the header
        $fromHeader = $this->kirby->request()->csrf();

        // check for a predefined csrf or use the one from session
        $fromSession = $this->kirby->option('api.csrf', csrf());

        // compare both tokens
        if (hash_equals((string)$fromSession, (string)$fromHeader) !== true) {
            return false;
        }

        return $fromSession;
    }

    /**
     * Returns the logged in user by checking
     * for a basic authentication header with
     * valid credentials
     *
     * @param BasicAuth|null $auth
     * @return User|null
     */
    public function currentUserFromBasicAuth(BasicAuth $auth = null)
    {
        if ($this->kirby->option('api.basicAuth', false) !== true) {
            throw new PermissionException('Basic authentication is not activated');
        }

        $request = $this->kirby->request();
        $auth    = $auth ?? $request->auth();

        if (!$auth || $auth->type() !== 'basic') {
            throw new InvalidArgumentException('Invalid authorization header');
        }

        // only allow basic auth when https is enabled
        if ($request->ssl() === false) {
            throw new PermissionException('Basic authentication is only allowed over HTTPS');
        }

        if ($user = $this->kirby->users()->find($auth->username())) {
            if ($user->validatePassword($auth->password()) === true) {
                return $user;
            }
        }

        return null;
    }

    /**
     * Returns the logged in user by checking
     * the current session and finding a valid
     * valid user id in there
     *
     * @param Session|null $session
     * @return User|null
     */
    public function currentUserFromSession($session = null)
    {
        // use passed session options or session object if set
        if (is_array($session)) {
            $session = $this->kirby->session($session);
        }

        // try session in header or cookie
        if (is_a($session, 'Kirby\Session\Session') === false) {
            $session = $this->kirby->session(['detect' => true]);
        }

        $id = $session->data()->get('user.id');

        if (is_string($id) !== true) {
            return null;
        }

        if ($user = $this->kirby->users()->find($id)) {
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
     * @return User|null
     */
    public function impersonate(string $who = null)
    {
        switch ($who) {
            case null:
                return $this->impersonate = null;
            case 'kirby':
                return $this->impersonate = new User([
                    'email' => 'kirby@getkirby.com',
                    'id'    => 'kirby',
                    'role'  => 'admin',
                ]);
            default:
                if ($user = $this->kirby->users()->find($who)) {
                    return $this->impersonate = $user;
                }

                throw new NotFoundException('The user "' . $who . '" cannot be found');
        }
    }

    /**
     * Login a user by email and password
     *
     * @param string $email
     * @param string $password
     * @param boolean $long
     * @return User|false
     */
    public function login(string $email, string $password, bool $long = false)
    {
        // stop impersonating
        $this->impersonate = null;

        // session options
        $options = [
            'createMode' => 'cookie',
            'long'       => $long === true
        ];

        // validate the user and log in to the session
        if ($user = $this->kirby->users()->find($email)) {
            if ($user->login($password, $options) === true) {
                return $this->user = $user;
            }
        }

        // sleep for a random amount of milliseconds
        // to make automated attacks harder
        usleep(random_int(1000, 2000000));

        return false;
    }

    /**
     * Logout the current user
     *
     * @return boolean
     */
    public function logout(): bool
    {
        // stop impersonating
        $this->impersonate = null;

        // logout the current user if it exists
        if ($user = $this->user()) {
            $user->logout();
        }

        $this->user = null;
        return true;
    }

    /**
     * Returns the current authentication type
     *
     * @return string
     */
    public function type(): string
    {
        $basicAuth = $this->kirby->option('api.basicAuth', false);
        $auth      = $this->kirby->request()->auth();

        if ($basicAuth === true && $auth && $auth->type() === 'basic') {
            return 'basic';
        } elseif ($this->impersonate !== null) {
            return 'impersonate';
        } else {
            return 'session';
        }
    }

    /**
     * Validates the currently logged in user
     *
     * @param array|Session|null $session
     * @return User|null
     */
    public function user($session = null): ?User
    {
        if ($this->impersonate !== null) {
            return $this->impersonate;
        }

        try {
            if ($this->type() === 'basic') {
                return $this->user = $this->currentUserFromBasicAuth();
            } else {
                return $this->user = $this->currentUserFromSession($session);
            }
        } catch (Throwable $e) {
            return $this->user = null;
        }
    }

}
