<?php

namespace Kirby\Cms;

use Kirby\Auth\Csrf;
use Kirby\Auth\Exception\LoginNotPermittedException;
use Kirby\Auth\Exception\RateLimitException;
use Kirby\Auth\Limits;
use Kirby\Auth\Methods;
use Kirby\Cms\Auth\Challenge;
use Kirby\Cms\Auth\Status;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Http\Idn;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Session\Session;
use Kirby\Toolkit\A;
use SensitiveParameter;
use Throwable;

/**
 * Authentication layer
 *
 * @package   Kirby Cms
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://getkirby.com/license
 */
class Auth
{
	/**
	 * Available auth challenge classes
	 * from the core and plugins
	 */
	public static array $challenges = [];

	protected Csrf $csrf;

	/**
	 * Currently impersonated user
	 */
	protected User|null $impersonate = null;

	protected Limits $limits;
	protected Methods $methods;

	/**
	 * Cache of the auth status object
	 */
	protected Status|null $status = null;

	/**
	 * Instance of the currently logged in user or
	 * `false` if the user was not yet determined
	 */
	protected User|false|null $user = false;

	/**
	 * Exception that was thrown while
	 * determining the current user
	 */
	protected Throwable|null $userException = null;

	/**
	 * @codeCoverageIgnore
	 */
	public function __construct(
		protected App $kirby
	) {
		$this->csrf    = new Csrf($kirby);
		$this->limits  = new Limits($kirby);
		$this->methods = new Methods($this, $kirby);
	}

	/**
	 * Login a user with email and (maybe optional) password
	 * as well as an auth challenge, if required by the auth method
	 *
	 * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded if any other error occurred with debug mode off
	 * @throws \Kirby\Exception\NotFoundException If the email was invalid
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->login()`)
	 */
	public function authenticate(
		string $method,
		string $email,
		#[SensitiveParameter]
		string|null $password = null,
		bool $long = false
	): User|Status {
		$result = $this->methods()->authenticate(
			type:      $method,
			email:     $email,
			password:  $password,
			long:      $long
		);

		if ($result instanceof User === true) {
			$this->setUser($result);
		}

		return $result;
	}

	/**
	 * Creates an authentication challenge
	 * (one-time auth code)
	 * @since 3.5.0
	 *
	 * @param bool $long If `true`, a long session will be created
	 * @param 'login'|'password-reset'|'2fa' $mode Purpose of the code
	 *
	 * @throws \Kirby\Exception\LogicException If there is no suitable authentication challenge (only in debug mode)
	 * @throws \Kirby\Exception\NotFoundException If the user does not exist (only in debug mode)
	 * @throws \Kirby\Exception\PermissionException If the rate limit is exceeded
	 */
	public function createChallenge(
		string $email,
		bool $long = false,
		string $mode = 'login'
	): Status {
		$email = Idn::decodeEmail($email);

		$session = $this->kirby->session([
			'createMode' => 'cookie',
			'long'       => $long === true
		]);

		$timeout = $this->kirby->option('auth.challenge.timeout', 10 * 60);

		// catch every exception to hide them from attackers
		// unless auth debugging is enabled
		try {
			// rate-limit the number of challenges for DoS/DDoS protection
			$this->limits->ensure($email);
			$this->limits->track($email, triggerHook: false);

			// try to find the provided user
			$user = $this->kirby->user($email);

			if ($user === null) {
				$this->kirby->trigger('user.login:failed', compact('email'));

				throw new NotFoundException(
					key: 'user.notFound',
					data: ['name' => $email]
				);
			}

			// try to find an enabled challenge that is available for that user
			$challenge = null;
			foreach ($this->enabledChallenges() as $name) {
				if (
					($class = static::$challenges[$name] ?? null) &&
					is_subclass_of($class, Challenge::class) === true &&
					$class::isAvailable($user, $mode) === true
				) {
					$challenge = $name;
					$code = $class::create($user, compact('mode', 'timeout'));

					$session->set('kirby.challenge.type', $challenge);

					if ($code !== null) {
						$session->set(
							'kirby.challenge.code',
							password_hash($code, PASSWORD_DEFAULT)
						);
					}

					break;
				}
			}

			// if no suitable challenge was found, `$challenge === null` at this point
			if ($challenge === null) {
				throw new LogicException(
					message: 'Could not find a suitable authentication challenge'
				);
			}

		} catch (Throwable $e) {
			// only throw the exception in auth debug mode
			$this->fail($e);
		}

		// always set the email, mode and timeout, even if the challenge
		// won't be created; this avoids leaking whether the user exists
		$session->set('kirby.challenge.email', $email);
		$session->set('kirby.challenge.mode', $mode);
		$session->set('kirby.challenge.timeout', time() + $timeout);

		// sleep for a random amount of milliseconds
		// to make automated attacks harder and to
		// avoid leaking whether the user exists
		usleep(random_int(50000, 300000));

		// clear the status cache
		$this->status = null;

		return $this->status($session, false);
	}

	/**
	 * Returns the csrf token if it exists and if it is valid
	 */
	public function csrf(): string|false
	{
		return $this->csrf->get();
	}

	/**
	 * Returns either predefined csrf or the one from session
	 * @since 3.6.0
	 */
	public function csrfFromSession(): string
	{
		return $this->csrf->fromSession();
	}

	/**
	 * Returns the logged in user by checking
	 * for a basic authentication header with
	 * valid credentials
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException if the authorization header is invalid
	 * @throws \Kirby\Exception\PermissionException if basic authentication is not allowed
	 */
	public function currentUserFromBasicAuth(
		BasicAuth|null $auth = null
	): User|null {
		if ($this->kirby->option('api.basicAuth', false) !== true) {
			throw new PermissionException(
				message: 'Basic authentication is not activated'
			);
		}

		// if logging in with password is disabled,
		// basic auth cannot be possible either
		$loginMethods = $this->kirby->system()->loginMethods();

		if (isset($loginMethods['password']) !== true) {
			throw new PermissionException(
				'Login with password is not enabled'
			);
		}

		// if any login method requires 2FA,
		// basic auth without 2FA would be a weakness
		foreach ($loginMethods as $method) {
			if (isset($method['2fa']) === true && $method['2fa'] === true) {
				throw new PermissionException(
					'Basic authentication cannot be used with 2FA'
				);
			}
		}

		$request = $this->kirby->request();
		$auth  ??= $request->auth();

		if (!$auth || $auth->type() !== 'basic') {
			throw new InvalidArgumentException(
				'Invalid authorization header'
			);
		}

		// only allow basic auth when https is enabled or
		// insecure requests permitted
		if (
			$request->ssl() === false &&
			$this->kirby->option('api.allowInsecure', false) !== true
		) {
			throw new PermissionException(
				message: 'Basic authentication is only allowed over HTTPS'
			);
		}

		/**
		 * @var \Kirby\Http\Request\Auth\BasicAuth $auth
		 */
		return $this->validatePassword($auth->username(), $auth->password());
	}

	/**
	 * Returns the currently impersonated user
	 */
	public function currentUserFromImpersonation(): User|null
	{
		return $this->impersonate;
	}

	/**
	 * Returns the logged in user by checking
	 * the current session and finding a valid
	 * valid user id in there
	 */
	public function currentUserFromSession(
		Session|array|null $session = null
	): User|null {
		$session = $this->session($session);

		$id = $session->data()->get('kirby.userId');

		// if no user is logged in, return immediately
		if (is_string($id) !== true) {
			return null;
		}

		// a user is logged in, ensure it exists
		$user = $this->kirby->users()->find($id);

		if ($user === null) {
			return null;
		}

		if ($passwordTimestamp = $user->passwordTimestamp()) {
			$loginTimestamp = $session->data()->get('kirby.loginTimestamp');

			if (is_int($loginTimestamp) !== true) {
				// session that was created before Kirby
				// 3.5.8.3, 3.6.6.3, 3.7.5.2, 3.8.4.1 or 3.9.6
				// or when the user didn't have a password set
				$user->logout();
				return null;
			}

			// invalidate the session if the password
			// changed since the login
			if ($loginTimestamp < $passwordTimestamp) {
				$user->logout();
				return null;
			}
		}

		// in case the session needs to be updated, do it now
		// for better performance
		$session->commit();
		return $user;
	}

	/**
	 * Returns the list of enabled challenges in the
	 * configured order
	 * @since 3.5.1
	 */
	public function enabledChallenges(): array
	{
		return A::wrap(
			$this->kirby->option('auth.challenges', ['totp', 'email'])
		);
	}

	/**
	 * Throws an exception only in debug mode, otherwise falls back
	 * to a public error without sensitive information
	 *
	 * @throws \Throwable Either the passed `$exception` or the `$fallback`
	 *                    (no exception if debugging is disabled and no fallback was passed)
	 */
	protected function fail(
		Throwable $exception,
		Throwable|null $fallback = null
	): void {
		$debug = $this->kirby->option('auth.debug', 'log');

		// throw the original exception only in debug mode
		if ($debug === true) {
			throw $exception;
		}

		// otherwise hide the real error and only print it to the error log
		// unless disabled by setting `auth.debug` to `false`
		if ($debug === 'log') {
			error_log($exception); // @codeCoverageIgnore
		}

		// only throw an error in production if requested by the calling method
		if ($fallback !== null) {
			throw $fallback;
		}
	}

	/**
	 * Clears the cached user data after logout
	 */
	public function flush(): void
	{
		$this->impersonate = null;
		$this->status      = null;
		$this->user        = null;
	}

	/**
	 * Become any existing user or disable the current user
	 *
	 * @param string|null $who User ID or email address,
	 *                         `null` to use the actual user again,
	 *                         `'kirby'` for a virtual admin user or
	 *                         `'nobody'` to disable the actual user
	 * @throws \Kirby\Exception\NotFoundException if the given user cannot be found
	 */
	public function impersonate(string|null $who = null): User|null
	{
		// clear the status cache
		$this->status = null;

		return $this->impersonate = match ($who) {
			null     => null,
			'kirby'  => new User([
				'email' => 'kirby@getkirby.com',
				'id'    => 'kirby',
				'role'  => 'admin',
			]),
			'nobody' => new User([
				'email' => 'nobody@getkirby.com',
				'id'    => 'nobody',
				'role'  => 'nobody',
			]),
			default => $this->kirby->users()->find($who) ?? throw new NotFoundException(
				message: 'The user "' . $who . '" cannot be found'
			),
		};
	}

	/**
	 * Check if logins are blocked for the current ip or email
	 * @deprecated 6.0.0 Use `self::limits()->Blocked()` instead
	 */
	public function isBlocked(string $email): bool
	{
		return $this->limits->isBlocked($email);
	}

	/**
	 * @since 6.0.0
	 */
	public function kirby(): App
	{
		return $this->kirby;
	}

	/**
	 * Returns the auth rate limits object
	 * @since 6.0.0
	 */
	public function limits(): Limits
	{
		return $this->limits;
	}

	/**
	 * Read all tracked logins
	 * @deprecated 6.0.0 Use `self::limits()->log()` instead
	 */
	public function log(): array
	{
		return $this->limits->log();
	}

	/**
	 * Returns the absolute path to the logins log
	 * @deprecated 6.0.0 Use `self::limits()->file()` instead
	 */
	public function logfile(): string
	{
		return $this->limits->file();
	}

	/**
	 * Login a user by email and password
	 *
	 * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded or if any other error occurred with debug mode off
	 * @throws \Kirby\Exception\NotFoundException If the email was invalid
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->login()`)
	 */
	public function login(
		string $email,
		#[SensitiveParameter]
		string $password,
		bool $long = false
	): User {
		$user = $this->authenticate('password', $email, $password, $long);

		if ($user instanceof User === false) {
			// if a method returned a pending status here,
			// it's a misconfiguration (e.g. password + 2FA active);
			// keep the existing login signature strict
			throw new LoginNotPermittedException(); // @codeCoverageIgnore
		}

		return $user;
	}

	/**
	 * Login a user by email, password and auth challenge
	 * @since 3.5.0
	 *
	 * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded or if any other error occurred with debug mode off
	 * @throws \Kirby\Exception\NotFoundException If the email was invalid
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->login()`)
	 *
	 * @deprecated 6.0.0 Use `self::authenticate()` instead
	 */
	public function login2fa(
		string $email,
		#[SensitiveParameter]
		string $password,
		bool $long = false
	): Status {
		$this->validatePassword($email, $password);
		return $this->createChallenge($email, $long, '2fa');
	}

	/**
	 * Logout the current user
	 */
	public function logout(): void
	{
		// stop impersonating;
		// ensures that we log out the actually logged in user
		$this->impersonate = null;

		// logout the current user if it exists
		$this->user()?->logout();

		// clear the pending challenge
		$session = $this->kirby->session();
		$session->remove('kirby.challenge.code');
		$session->remove('kirby.challenge.email');
		$session->remove('kirby.challenge.mode');
		$session->remove('kirby.challenge.timeout');
		$session->remove('kirby.challenge.type');

		// clear the status cache
		$this->status = null;
	}

	/**
	 * Returns the auth methods handler
	 * @since 6.0.0
	 */
	public function methods(): Methods
	{
		return $this->methods;
	}

	/**
	 * Creates a session object from the passed options
	 */
	protected function session(Session|array|null $session = null): Session
	{
		// use passed session options or session object if set
		if (is_array($session) === true) {
			return $this->kirby->session($session);
		}

		// try session in header or cookie
		if ($session instanceof Session === false) {
			return $this->kirby->session(['detect' => true]);
		}

		return $session;
	}

	/**
	 * Sets a user object as the current user in the cache
	 * @internal
	 */
	public function setUser(User $user): void
	{
		// stop impersonating
		$this->impersonate = null;
		$this->user        = $user;

		// clear the status cache
		$this->status = null;
	}

	/**
	 * Returns the authentication status object
	 * @since 3.5.1
	 *
	 * @param bool $allowImpersonation If set to false, only the actually
	 *                                 logged in user will be returned
	 */
	public function status(
		Session|array|null $session = null,
		bool $allowImpersonation = true
	): Status {
		// try to return from cache
		if (
			$this->status &&
			$session === null &&
			$allowImpersonation === true
		) {
			return $this->status;
		}

		$sessionObj = $this->session($session);

		$props = ['kirby' => $this->kirby];

		if ($user = $this->user($sessionObj, $allowImpersonation)) {
			// a user is currently logged in
			$props['email']  = $user->email();
			$props['status'] = match (true) {
				$allowImpersonation === true &&
					$this->impersonate !== null  => 'impersonated',
				default                      => 'active'
			};
		} elseif ($email = $sessionObj->get('kirby.challenge.email')) {
			// a challenge is currently pending
			$props['status']            = 'pending';
			$props['email']             = $email;
			$props['mode']              = $sessionObj->get('kirby.challenge.mode');
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
	 * Tracks a login
	 *
	 * @param bool $triggerHook If `false`, no user.login:failed hook is triggered
	 * @deprecated 6.0.0 Use `self::limits()->track()` instead
	 */
	public function track(
		string|null $email,
		bool $triggerHook = true
	): bool {
		return $this->limits->track($email, $triggerHook);
	}

	/**
	 * Returns the current authentication type
	 *
	 * @param bool $allowImpersonation If set to false, 'impersonate' won't
	 *                                 be returned as authentication type
	 *                                 even if an impersonation is active
	 */
	public function type(bool $allowImpersonation = true): string
	{
		$basicAuth = $this->kirby->option('api.basicAuth', false);
		$request   = $this->kirby->request();

		if (
			$basicAuth === true &&

			// only get the auth object if the option is enabled
			// to avoid triggering `$responder->usesAuth()` if
			// the option is disabled
			$request->auth() &&
			$request->auth()->type() === 'basic'
		) {
			return 'basic';
		}

		if ($allowImpersonation === true && $this->impersonate !== null) {
			return 'impersonate';
		}

		return 'session';
	}

	/**
	 * Validates the currently logged in user
	 *
	 * @param bool $allowImpersonation If set to false, only the actually
	 *                                 logged in user will be returned
	 *
	 * @throws \Throwable If an authentication error occurred
	 */
	public function user(
		Session|array|null $session = null,
		bool $allowImpersonation = true
	): User|null {
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
		}

		if ($this->user !== false) {
			return $this->user;
		}

		try {
			if ($this->type() === 'basic') {
				return $this->user = $this->currentUserFromBasicAuth();
			}

			return $this->user = $this->currentUserFromSession($session);
		} catch (Throwable $e) {
			$this->user = null;

			// capture the Exception for future calls
			$this->userException = $e;

			throw $e;
		}
	}

	/**
	 * Validates the user credentials and returns the user object on success;
	 * otherwise logs the failed attempt
	 *
	 * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded or if any other error occurred with debug mode off
	 * @throws \Kirby\Exception\NotFoundException If the email was invalid
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->login()`)
	 */
	public function validatePassword(
		string $email,
		#[SensitiveParameter]
		string $password
	): User {
		$email = Idn::decodeEmail($email);

		try {
			$this->limits->ensure($email);

			// validate the user and its password
			if ($user = $this->kirby->users()->find($email)) {
				if ($user->validatePassword($password) === true) {
					return $user;
				}
			}

			throw new NotFoundException(
				key: 'user.notFound',
				data: ['name' => $email]
			);

		} catch (Throwable $e) {
			// log invalid login trial unless the rate limit is already active
			if ($e instanceof RateLimitException === false) {
				try {
					$this->limits->track($email);
				} catch (Throwable) {
					// $e is overwritten with the exception
					// from the track method if there's one
				}
			}

			// sleep for a random amount of milliseconds
			// to make automated attacks harder
			usleep(random_int(10000, 2000000));

			// keep throwing the original error in debug mode,
			// otherwise hide it to avoid leaking security-relevant information
			$this->fail($e, new PermissionException(key: 'access.login'));
		}
	}

	/**
	 * Verifies an authentication code that was
	 * requested with the `createChallenge()` method;
	 * if successful, the user is automatically logged in
	 * @since 3.5.0
	 *
	 * @param string $code User-provided auth code to verify
	 * @return \Kirby\Cms\User User object of the logged-in user
	 *
	 * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded, the challenge timed out, the code
	 *                                              is incorrect or if any other error occurred with debug mode off
	 * @throws \Kirby\Exception\NotFoundException If the user from the challenge doesn't exist
	 * @throws \Kirby\Exception\InvalidArgumentException If no authentication challenge is active
	 * @throws \Kirby\Exception\LogicException If the authentication challenge is invalid
	 */
	public function verifyChallenge(
		#[SensitiveParameter]
		string $code
	): User {
		try {
			$session = $this->kirby->session();

			// time-limiting; check this early so that we can
			// destroy the session no matter if the user exists
			// (avoids leaking user information to attackers)
			$timeout = $session->get('kirby.challenge.timeout');

			if ($timeout !== null && time() > $timeout) {
				// this challenge can never be completed,
				// so delete it immediately
				$this->logout();

				throw new PermissionException(
					details: ['challengeDestroyed' => true],
					fallback: 'Authentication challenge timeout'
				);
			}

			// check if we have an active challenge
			$email     = $session->get('kirby.challenge.email');
			$challenge = $session->get('kirby.challenge.type');

			if (is_string($email) !== true || is_string($challenge) !== true) {
				// if the challenge timed out on the previous request, the
				// challenge data was already deleted from the session, so we can
				// set `challengeDestroyed` to `true` in this response as well;
				// however we must only base this on the email, not the type
				// (otherwise "faked" challenges would be leaked)
				$challengeDestroyed = is_string($email) !== true;

				throw new InvalidArgumentException(
					details: compact('challengeDestroyed'),
					fallback: 'No authentication challenge is active'
				);
			}

			$user = $this->kirby->users()->find($email);

			if ($user === null) {
				throw new NotFoundException(
					key: 'user.notFound',
					data: ['name' => $email]
				);
			}

			// rate-limiting
			$this->limits->ensure($email);

			if (
				isset(static::$challenges[$challenge]) === true &&
				is_subclass_of(static::$challenges[$challenge], Challenge::class) === true
			) {
				$class = static::$challenges[$challenge];

				if ($class::verify($user, $code) === true) {
					$mode = $session->get('kirby.challenge.mode');

					$this->logout();
					$user->loginPasswordless();

					// allow the user to set a new password without knowing the previous one
					if ($mode === 'password-reset') {
						$session->set('kirby.resetPassword', true);
					}

					// clear the status cache
					$this->status = null;

					return $user;
				}

				throw new PermissionException(key: 'access.code');
			}

			throw new LogicException(
				message: 'Invalid authentication challenge: ' . $challenge
			);

		} catch (Throwable $e) {
			if (
				empty($email) === false &&
				$e instanceof RateLimitException === false
			) {
				$this->limits->track($email);
			}

			// sleep for a random amount of milliseconds
			// to make automated attacks harder and to
			// avoid leaking whether the user exists
			usleep(random_int(10000, 2000000));

			// specifically copy over the marker for a destroyed challenge
			// even in production (used by the Panel to reset to the login form)
			$details = $e instanceof Exception ? $e->getDetails() : [];
			$challengeDestroyed = $details['challengeDestroyed'] ?? false;

			$fallback = new PermissionException(
				details: compact('challengeDestroyed'),
				key: 'access.code'
			);

			// keep throwing the original error in debug mode,
			// otherwise hide it to avoid leaking security-relevant information
			$this->fail($e, $fallback);
		}
	}
}
