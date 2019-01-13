<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\PermissionException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Session\Session;
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
     * Returns the hashed ip of the visitor
     * which is used to track invalid logins
     *
     * @return string
     */
    public function ipHash(): string
    {
        return hash('sha256', $this->kirby->visitor()->ip());
    }

    /**
     * Check if logins are blocked for the current ip
     *
     * @return boolean
     */
    public function isBlocked(): bool
    {
        $ip      = $this->ipHash();
        $log     = $this->log();
        $trials  = $this->kirby->option('auth.trials', 10);
        $timeout = $this->kirby->option('auth.timeout', 3600);

        if ($entry = ($log[$ip] ?? null)) {
            if ($entry['trials'] > $trials) {
                if ($entry['time'] > (time() - $timeout)) {
                    return true;
                }
            }
        }

        return false;
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
        // check for blocked ips
        if ($this->isBlocked() === true) {
            throw new PermissionException('Rate limit exceeded', 403);
        }

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

        // log invalid login trial
        $this->track();

        // sleep for a random amount of milliseconds
        // to make automated attacks harder
        usleep(random_int(1000, 2000000));

        return false;
    }

    /**
     * Returns the absolute path to the logins log
     *
     * @return string
     */
    public function logfile(): string
    {
        return $this->kirby->root('accounts') . '/.logins';
    }

    /**
     * Read all tracked logins
     *
     * @return array
     */
    public function log(): array
    {
        try {
            return Data::read($this->logfile(), 'json');
        } catch (Throwable $e) {
            return [];
        }
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
     * Tracks a login
     *
     * @return boolean
     */
    public function track(): bool
    {
        $ip   = $this->ipHash();
        $log  = $this->log();
        $time = time();

        if (isset($log[$ip]) === true) {
            $log[$ip] = [
                'time'   => $time,
                'trials' => ($log[$ip]['trials'] ?? 0) + 1
            ];
        } else {
            $log[$ip] = [
                'time'   => $time,
                'trials' => 1
            ];
        }

        return Data::write($this->logfile(), $log, 'json');
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
