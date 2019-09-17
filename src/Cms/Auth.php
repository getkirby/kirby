<?php

namespace Kirby\Cms;

use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Toolkit\F;
use Throwable;

/**
 * Authentication layer
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier GmbH
 * @license   https://getkirby.com/license
 */
class Auth
{
    protected $impersonate;
    protected $kirby;
    protected $user = false;
    protected $userException;

    /**
     * @param \Kirby\Cms\App $kirby
     * @codeCoverageIgnore
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
     * @param \Kirby\Http\Request\Auth\BasicAuth|null $auth
     * @return \Kirby\Cms\User|null
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

        // only allow basic auth when https is enabled or insecure requests permitted
        if ($request->ssl() === false && $this->kirby->option('api.allowInsecure', false) !== true) {
            throw new PermissionException('Basic authentication is only allowed over HTTPS');
        }

        return $this->validatePassword($auth->username(), $auth->password());
    }

    /**
     * Returns the logged in user by checking
     * the current session and finding a valid
     * valid user id in there
     *
     * @param \Kirby\Session\Session|array|null $session
     * @return \Kirby\Cms\User|null
     */
    public function currentUserFromSession($session = null)
    {
        // use passed session options or session object if set
        if (is_array($session) === true) {
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
     * @return \Kirby\Cms\User|null
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
        $hash = hash('sha256', $this->kirby->visitor()->ip());

        // only use the first 50 chars to ensure privacy
        return substr($hash, 0, 50);
    }

    /**
     * Check if logins are blocked for the current ip or email
     *
     * @param string $email
     * @return boolean
     */
    public function isBlocked(string $email): bool
    {
        $ip     = $this->ipHash();
        $log    = $this->log();
        $trials = $this->kirby->option('auth.trials', 10);

        if ($entry = ($log['by-ip'][$ip] ?? null)) {
            if ($entry['trials'] >= $trials) {
                return true;
            }
        }

        if ($this->kirby->users()->find($email)) {
            if ($entry = ($log['by-email'][$email] ?? null)) {
                if ($entry['trials'] >= $trials) {
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
     * @return \Kirby\Cms\User
     *
     * @throws PermissionException If the rate limit was exceeded or if any other error occured with debug mode off
     * @throws NotFoundException If the email was invalid
     * @throws InvalidArgumentException If the password is not valid (via `$user->login()`)
     */
    public function login(string $email, string $password, bool $long = false)
    {
        // session options
        $options = [
            'createMode' => 'cookie',
            'long'       => $long === true
        ];

        // validate the user and log in to the session
        $user = $this->validatePassword($email, $password);
        $user->loginPasswordless($options);

        // stop impersonating
        $this->impersonate = null;

        return $this->user = $user;
    }

    /**
     * Validates the user credentials and returns the user object on success;
     * otherwise logs the failed attempt
     *
     * @param string $email
     * @param string $password
     * @return \Kirby\Cms\User
     *
     * @throws PermissionException If the rate limit was exceeded or if any other error occured with debug mode off
     * @throws NotFoundException If the email was invalid
     * @throws InvalidArgumentException If the password is not valid (via `$user->login()`)
     */
    public function validatePassword(string $email, string $password)
    {
        // check for blocked ips
        if ($this->isBlocked($email) === true) {
            if ($this->kirby->option('debug') === true) {
                $message = 'Rate limit exceeded';
            } else {
                // avoid leaking security-relevant information
                $message = 'Invalid email or password';
            }

            throw new PermissionException($message, 403);
        }

        // validate the user
        try {
            if ($user = $this->kirby->users()->find($email)) {
                if ($user->validatePassword($password) === true) {
                    return $user;
                }
            }

            throw new NotFoundException([
                'key'  => 'user.notFound',
                'data' => [
                    'name' => $email
                ]
            ]);
        } catch (Throwable $e) {
            // log invalid login trial
            $this->track($email);

            // sleep for a random amount of milliseconds
            // to make automated attacks harder
            usleep(random_int(1000, 2000000));

            // keep throwing the original error in debug mode,
            // otherwise hide it to avoid leaking security-relevant information
            if ($this->kirby->option('debug') === true) {
                throw $e;
            } else {
                throw new PermissionException('Invalid email or password');
            }
        }
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
            $log  = Data::read($this->logfile(), 'json');
            $read = true;
        } catch (Throwable $e) {
            $log  = [];
            $read = false;
        }

        // ensure that the category arrays are defined
        $log['by-ip']    = $log['by-ip'] ?? [];
        $log['by-email'] = $log['by-email'] ?? [];

        // remove entries that are no longer needed
        $originalLog = $log;
        $time = time() - $this->kirby->option('auth.timeout', 3600);
        foreach ($log as $category => $entries) {
            $log[$category] = array_filter($entries, function ($entry) use ($time) {
                return $entry['time'] > $time;
            });
        }

        // remove all elements on the top level with different keys (old structure)
        $log = array_intersect_key($log, array_flip(['by-ip', 'by-email']));

        // write new log to the file system if it changed
        if ($read === false || $log !== $originalLog) {
            if (count($log['by-ip']) === 0 && count($log['by-email']) === 0) {
                F::remove($this->logfile());
            } else {
                Data::write($this->logfile(), $log, 'json');
            }
        }

        return $log;
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
     * @param string $email
     * @return boolean
     */
    public function track(string $email): bool
    {
        $ip   = $this->ipHash();
        $log  = $this->log();
        $time = time();

        if (isset($log['by-ip'][$ip]) === true) {
            $log['by-ip'][$ip] = [
                'time'   => $time,
                'trials' => ($log['by-ip'][$ip]['trials'] ?? 0) + 1
            ];
        } else {
            $log['by-ip'][$ip] = [
                'time'   => $time,
                'trials' => 1
            ];
        }

        if ($this->kirby->users()->find($email)) {
            if (isset($log['by-email'][$email]) === true) {
                $log['by-email'][$email] = [
                    'time'   => $time,
                    'trials' => ($log['by-email'][$email]['trials'] ?? 0) + 1
                ];
            } else {
                $log['by-email'][$email] = [
                    'time'   => $time,
                    'trials' => 1
                ];
            }
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
     * @param \Kirby\Session\Session|array|null $session
     * @return \Kirby\Cms\User
     * @throws
     */
    public function user($session = null)
    {
        if ($this->impersonate !== null) {
            return $this->impersonate;
        }

        // return from cache
        if ($this->user === null) {
            // throw the same Exception again if one was captured before
            if ($this->userException !== null) {
                throw $this->userException;
            }

            return null;
        } elseif ($this->user !== false) {
            return $this->user;
        }

        try {
            if ($this->type() === 'basic') {
                return $this->user = $this->currentUserFromBasicAuth();
            } else {
                return $this->user = $this->currentUserFromSession($session);
            }
        } catch (Throwable $e) {
            $this->user = null;

            // capture the Exception for future calls
            $this->userException = $e;

            throw $e;
        }
    }
}
