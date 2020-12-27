<?php

namespace Kirby\Cms;

use Kirby\Cms\Auth\Status;
use Kirby\Data\Data;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Http\Idn;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Toolkit\A;
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
    /**
     * Available auth challenge classes
     * from the core and plugins
     *
     * @var array
     */
    public static $challenges = [];

    /**
     * Currently impersonated user
     *
     * @var \Kirby\Cms\User|null
     */
    protected $impersonate;

    /**
     * Kirby instance
     *
     * @var \Kirby\Cms\App
     */
    protected $kirby;

    /**
     * Cache of the auth status object
     *
     * @var \Kirby\Cms\Auth\Status
     */
    protected $status;

    /**
     * Instance of the currently logged in user or
     * `false` if the user was not yet determined
     *
     * @var \Kirby\Cms\User|null|false
     */
    protected $user = false;

    /**
     * Exception that was thrown while
     * determining the current user
     *
     * @var \Throwable
     */
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
     * Creates an authentication challenge
     * (one-time auth code)
     *
     * @param string $email
     * @param bool $long If `true`, a long session will be created
     * @param string $mode Either 'login' or 'password-reset'
     * @return \Kirby\Cms\Auth\Status
     *
     * @throws \Kirby\Exception\LogicException If there is no suitable authentication challenge (only in debug mode)
     * @throws \Kirby\Exception\NotFoundException If the user does not exist (only in debug mode)
     * @throws \Kirby\Exception\PermissionException If the rate limit is exceeded
     */
    public function createChallenge(string $email, bool $long = false, string $mode = 'login')
    {
        // ensure that email addresses with IDN domains are in Unicode format
        $email = Idn::decodeEmail($email);

        if ($this->isBlocked($email) === true) {
            $this->kirby->trigger('user.login:failed', compact('email'));

            if ($this->kirby->option('debug') === true) {
                $message = 'Rate limit exceeded';
            } else {
                // avoid leaking security-relevant information
                $message = ['key' => 'access.login'];
            }

            throw new PermissionException($message);
        }

        // rate-limit the number of challenges for DoS/DDoS protection
        $this->track($email, false);

        $session = $this->kirby->session([
            'createMode' => 'cookie',
            'long'       => $long === true
        ]);

        $challenge = null;
        if ($user = $this->kirby->users()->find($email)) {
            $timeout = $this->kirby->option('auth.challenge.timeout', 10 * 60);

            foreach ($this->enabledChallenges() as $name) {
                $class = static::$challenges[$name] ?? null;
                if (
                    $class &&
                    class_exists($class) === true &&
                    is_subclass_of($class, 'Kirby\Cms\Auth\Challenge') === true &&
                    $class::isAvailable($user, $mode) === true
                ) {
                    $challenge = $name;
                    $code = $class::create($user, compact('mode', 'timeout'));

                    $session->set('kirby.challenge.type', $challenge);

                    if ($code !== null) {
                        $session->set('kirby.challenge.code', password_hash($code, PASSWORD_DEFAULT));
                        $session->set('kirby.challenge.timeout', time() + $timeout);
                    }

                    break;
                }
            }

            // if no suitable challenge was found, `$challenge === null` at this point;
            // only leak this in debug mode
            if ($challenge === null && $this->kirby->option('debug') === true) {
                throw new LogicException('Could not find a suitable authentication challenge');
            }
        } else {
            $this->kirby->trigger('user.login:failed', compact('email'));

            // only leak the non-existing user in debug mode
            if ($this->kirby->option('debug') === true) {
                throw new NotFoundException([
                    'key'  => 'user.notFound',
                    'data' => [
                        'name' => $email
                    ]
                ]);
            }
        }

        // always set the email, even if the challenge won't be
        // created to avoid leaking whether the user exists
        $session->set('kirby.challenge.email', $email);

        // sleep for a random amount of milliseconds
        // to make automated attacks harder and to
        // avoid leaking whether the user exists
        usleep(random_int(1000, 300000));

        // clear the status cache
        $this->status = null;

        return $this->status($session, false);
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
     * @throws \Kirby\Exception\InvalidArgumentException if the authorization header is invalid
     * @throws \Kirby\Exception\PermissionException if basic authentication is not allowed
     */
    public function currentUserFromBasicAuth(BasicAuth $auth = null)
    {
        if ($this->kirby->option('api.basicAuth', false) !== true) {
            throw new PermissionException('Basic authentication is not activated');
        }

        // if logging in with password is disabled, basic auth cannot be possible either
        $loginMethods = $this->kirby->system()->loginMethods();
        if (isset($loginMethods['password']) !== true) {
            throw new PermissionException('Login with password is not enabled');
        }

        // if any login method requires 2FA, basic auth without 2FA would be a weakness
        foreach ($loginMethods as $method) {
            if (isset($method['2fa']) === true && $method['2fa'] === true) {
                throw new PermissionException('Basic authentication cannot be used with 2FA');
            }
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
     * Returns the currently impersonated user
     *
     * @return \Kirby\Cms\User|null
     */
    public function currentUserFromImpersonation()
    {
        return $this->impersonate;
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
        $session = $this->session($session);

        $id = $session->data()->get('kirby.userId');

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
     * Returns the list of enabled challenges in the
     * configured order
     *
     * @return array
     */
    public function enabledChallenges(): array
    {
        return A::wrap($this->kirby->option('auth.challenges', ['email']));
    }

    /**
     * Become any existing user or disable the current user
     *
     * @param string|null $who User ID or email address,
     *                         `null` to use the actual user again,
     *                         `'kirby'` for a virtual admin user or
     *                         `'nobody'` to disable the actual user
     * @return \Kirby\Cms\User|null
     * @throws \Kirby\Exception\NotFoundException if the given user cannot be found
     */
    public function impersonate(?string $who = null)
    {
        // clear the status cache
        $this->status = null;

        switch ($who) {
            case null:
                return $this->impersonate = null;
            case 'kirby':
                return $this->impersonate = new User([
                    'email' => 'kirby@getkirby.com',
                    'id'    => 'kirby',
                    'role'  => 'admin',
                ]);
            case 'nobody':
                return $this->impersonate = new User([
                    'email' => 'nobody@getkirby.com',
                    'id'    => 'nobody',
                    'role'  => 'nobody',
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
     * @return bool
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
     * @param bool $long
     * @return \Kirby\Cms\User
     *
     * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded or if any other error occured with debug mode off
     * @throws \Kirby\Exception\NotFoundException If the email was invalid
     * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->login()`)
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

        // clear the status cache
        $this->status = null;

        return $user;
    }

    /**
     * Login a user by email, password and auth challenge
     *
     * @param string $email
     * @param string $password
     * @param bool $long
     * @return \Kirby\Cms\Auth\Status
     *
     * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded or if any other error occured with debug mode off
     * @throws \Kirby\Exception\NotFoundException If the email was invalid
     * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->login()`)
     */
    public function login2fa(string $email, string $password, bool $long = false)
    {
        $this->validatePassword($email, $password);
        return $this->createChallenge($email, $long, '2fa');
    }

    /**
     * Sets a user object as the current user in the cache
     * @internal
     *
     * @param \Kirby\Cms\User $user
     * @return void
     */
    public function setUser(User $user): void
    {
        // stop impersonating
        $this->impersonate = null;

        $this->user = $user;

        // clear the status cache
        $this->status = null;
    }

    /**
     * Returns the authentication status object
     *
     * @param \Kirby\Session\Session|array|null $session
     * @param bool $allowImpersonation If set to false, only the actually
     *                                 logged in user will be returned
     * @return \Kirby\Cms\Auth\Status
     */
    public function status($session = null, bool $allowImpersonation = true)
    {
        // try to return from cache
        if ($this->status && $session === null && $allowImpersonation === true) {
            return $this->status;
        }

        $sessionObj = $this->session($session);

        $props = ['kirby' => $this->kirby];
        if ($user = $this->user($sessionObj, $allowImpersonation)) {
            // a user is currently logged in
            if ($allowImpersonation === true && $this->impersonate !== null) {
                $props['status'] = 'impersonated';
            } else {
                $props['status'] = 'active';
            }

            $props['email'] = $user->email();
        } elseif ($email = $sessionObj->get('kirby.challenge.email')) {
            // a challenge is currently pending
            $props['status']            = 'pending';
            $props['email']             = $email;
            $props['challenge']         = $sessionObj->get('kirby.challenge.type');
            $props['challengeFallback'] = A::last($this->enabledChallenges());
        } else {
            // no active authentication
            $props['status'] = 'inactive';
        }

        $status = new Status($props);

        // only cache the default object
        if ($session === null && $allowImpersonation === true) {
            $this->status = $status;
        }

        return $status;
    }

    /**
     * Validates the user credentials and returns the user object on success;
     * otherwise logs the failed attempt
     *
     * @param string $email
     * @param string $password
     * @return \Kirby\Cms\User
     *
     * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded or if any other error occured with debug mode off
     * @throws \Kirby\Exception\NotFoundException If the email was invalid
     * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->login()`)
     */
    public function validatePassword(string $email, string $password)
    {
        // ensure that email addresses with IDN domains are in Unicode format
        $email = Idn::decodeEmail($email);

        // check for blocked ips
        if ($this->isBlocked($email) === true) {
            $this->kirby->trigger('user.login:failed', compact('email'));

            if ($this->kirby->option('debug') === true) {
                $message = 'Rate limit exceeded';
            } else {
                // avoid leaking security-relevant information
                $message = ['key' => 'access.login'];
            }

            throw new PermissionException($message);
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
                throw new PermissionException(['key' => 'access.login']);
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

        // remove all elements on the top level with different keys (old structure)
        $log = array_intersect_key($log, array_flip(['by-ip', 'by-email']));

        // remove entries that are no longer needed
        $originalLog = $log;
        $time = time() - $this->kirby->option('auth.timeout', 3600);
        foreach ($log as $category => $entries) {
            $log[$category] = array_filter($entries, function ($entry) use ($time) {
                return $entry['time'] > $time;
            });
        }

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
     * @return void
     */
    public function logout(): void
    {
        // stop impersonating;
        // ensures that we log out the actually logged in user
        $this->impersonate = null;

        // logout the current user if it exists
        if ($user = $this->user()) {
            $user->logout();
        }

        // clear the pending challenge
        $session = $this->kirby->session();
        $session->remove('kirby.challenge.code');
        $session->remove('kirby.challenge.email');
        $session->remove('kirby.challenge.timeout');
        $session->remove('kirby.challenge.type');

        // clear the status cache
        $this->status = null;
    }

    /**
     * Clears the cached user data after logout
     * @internal
     *
     * @return void
     */
    public function flush(): void
    {
        $this->impersonate = null;
        $this->status = null;
        $this->user = null;
    }

    /**
     * Tracks a login
     *
     * @param string|null $email
     * @param bool $triggerHook If `false`, no user.login:failed hook is triggered
     * @return bool
     */
    public function track(?string $email, bool $triggerHook = true): bool
    {
        if ($triggerHook === true) {
            $this->kirby->trigger('user.login:failed', compact('email'));
        }

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

        if ($email !== null && $this->kirby->users()->find($email)) {
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
     * @param bool $allowImpersonation If set to false, 'impersonate' won't
     *                                 be returned as authentication type
     *                                 even if an impersonation is active
     * @return string
     */
    public function type(bool $allowImpersonation = true): string
    {
        $basicAuth = $this->kirby->option('api.basicAuth', false);
        $auth      = $this->kirby->request()->auth();

        if ($basicAuth === true && $auth && $auth->type() === 'basic') {
            return 'basic';
        } elseif ($allowImpersonation === true && $this->impersonate !== null) {
            return 'impersonate';
        } else {
            return 'session';
        }
    }

    /**
     * Validates the currently logged in user
     *
     * @param \Kirby\Session\Session|array|null $session
     * @param bool $allowImpersonation If set to false, only the actually
     *                                 logged in user will be returned
     * @return \Kirby\Cms\User|null
     *
     * @throws \Throwable If an authentication error occured
     */
    public function user($session = null, bool $allowImpersonation = true)
    {
        if ($allowImpersonation === true && $this->impersonate !== null) {
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

    /**
     * Verifies an authentication code that was
     * requested with the `createChallenge()` method;
     * if successful, the user is automatically logged in
     *
     * @param string $code User-provided auth code to verify
     * @return \Kirby\Cms\User User object of the logged-in user
     *
     * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded, the challenge timed out, the code
     *                                              is incorrect or if any other error occured with debug mode off
     * @throws \Kirby\Exception\NotFoundException If the user from the challenge doesn't exist
     * @throws \Kirby\Exception\InvalidArgumentException If no authentication challenge is active
     * @throws \Kirby\Exception\LogicException If the authentication challenge is invalid
     */
    public function verifyChallenge(string $code)
    {
        try {
            $session = $this->kirby->session();

            // first check if we have an active challenge at all
            $email     = $session->get('kirby.challenge.email');
            $challenge = $session->get('kirby.challenge.type');
            if (is_string($email) !== true || is_string($challenge) !== true) {
                throw new InvalidArgumentException('No authentication challenge is active');
            }

            $user = $this->kirby->users()->find($email);
            if ($user === null) {
                throw new NotFoundException([
                    'key'  => 'user.notFound',
                    'data' => [
                        'name' => $email
                    ]
                ]);
            }

            // rate-limiting
            if ($this->isBlocked($email) === true) {
                $this->kirby->trigger('user.login:failed', compact('email'));
                throw new PermissionException('Rate limit exceeded');
            }

            // time-limiting
            $timeout = $session->get('kirby.challenge.timeout');
            if ($timeout !== null && time() > $timeout) {
                throw new PermissionException('Authentication challenge timeout');
            }

            if (
                isset(static::$challenges[$challenge]) === true &&
                class_exists(static::$challenges[$challenge]) === true &&
                is_subclass_of(static::$challenges[$challenge], 'Kirby\Cms\Auth\Challenge') === true
            ) {
                $class = static::$challenges[$challenge];
                if ($class::verify($user, $code) === true) {
                    $this->logout();
                    $user->loginPasswordless();

                    // clear the status cache
                    $this->status = null;

                    return $user;
                } else {
                    throw new PermissionException(['key' => 'access.code']);
                }
            }
            
            throw new LogicException('Invalid authentication challenge: ' . $challenge);
        } catch (Throwable $e) {
            if ($e->getMessage() !== 'Rate limit exceeded') {
                $this->track($email);
            }

            // sleep for a random amount of milliseconds
            // to make automated attacks harder and to
            // avoid leaking whether the user exists
            usleep(random_int(1000, 2000000));

            // keep throwing the original error in debug mode,
            // otherwise hide it to avoid leaking security-relevant information
            if ($this->kirby->option('debug') === true) {
                throw $e;
            } else {
                throw new PermissionException(['key' => 'access.code']);
            }
        }
    }

    /**
     * Creates a session object from the passed options
     *
     * @param \Kirby\Session\Session|array|null $session
     * @return \Kirby\Session\Session
     */
    protected function session($session = null)
    {
        // use passed session options or session object if set
        if (is_array($session) === true) {
            return $this->kirby->session($session);
        }

        // try session in header or cookie
        if (is_a($session, 'Kirby\Session\Session') === false) {
            return $this->kirby->session(['detect' => true]);
        }

        return $session;
    }
}
