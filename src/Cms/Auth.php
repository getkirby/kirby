<?php

namespace Kirby\Cms;

use Kirby\Cms\Auth\Challenge;
use Kirby\Cms\Auth\Status;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Exception\PermissionException;
use Kirby\Filesystem\F;
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

	/**
	 * Currently impersonated user
	 */
	protected User|null $impersonate = null;

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
			$this->checkRateLimit($email);

			// rate-limit the number of challenges for DoS/DDoS protection
			$this->track($email, false);

			// try to find the provided user
			$user = $this->kirby->users()->find($email);
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
				$class = static::$challenges[$name] ?? null;
				if (
					$class &&
					class_exists($class) === true &&
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
					'Could not find a suitable authentication challenge'
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
		// get the csrf from the header
		$fromHeader = $this->kirby->request()->csrf();

		// check for a predefined csrf or use the one from session
		$fromSession = $this->csrfFromSession();

		// compare both tokens
		if (hash_equals((string)$fromSession, (string)$fromHeader) !== true) {
			return false;
		}

		return $fromSession;
	}

	/**
	 * Returns either predefined csrf or the one from session
	 * @since 3.6.0
	 */
	public function csrfFromSession(): string
	{
		$isDev    = $this->kirby->option('panel.dev', false) !== false;
		$fallback = $isDev ? 'dev' : $this->kirby->csrf();
		return $this->kirby->option('api.csrf', $fallback);
	}

	/**
	 * Returns the logged in user by checking
	 * for a basic authentication header with
	 * valid credentials
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException if the authorization header is invalid
	 * @throws \Kirby\Exception\PermissionException if basic authentication is not allowed
	 */
	public function currentUserFromBasicAuth(BasicAuth|null $auth = null): User|null
	{
		if ($this->kirby->option('api.basicAuth', false) !== true) {
			throw new PermissionException(
				'Basic authentication is not activated'
			);
		}

		// if logging in with password is disabled, basic auth cannot be possible either
		$loginMethods = $this->kirby->system()->loginMethods();
		if (isset($loginMethods['password']) !== true) {
			throw new PermissionException(
				'Login with password is not enabled'
			);
		}

		// if any login method requires 2FA, basic auth without 2FA would be a weakness
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
				'Basic authentication is only allowed over HTTPS'
			);
		}

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
			default => $this->kirby->users()->find($who) ?? throw new NotFoundException(message: 'The user "' . $who . '" cannot be found'),
		};
	}

	/**
	 * Returns the hashed ip of the visitor
	 * which is used to track invalid logins
	 * @deprecated 5.3.0 Use `$visitor->ip(hash: true)` instead. Will be removed in Kirby 6.
	 */
	public function ipHash(): string
	{
		$hash = hash('sha256', $this->kirby->visitor()->ip());

		// only use the first 50 chars to ensure privacy
		return substr($hash, 0, 50);
	}

	/**
	 * Check if logins are blocked for the current ip or email
	 */
	public function isBlocked(string $email): bool
	{
		$ip     = $this->kirby->visitor()->ip(hash: true);
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
	 * @since 3.5.0
	 *
	 * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded or if any other error occurred with debug mode off
	 * @throws \Kirby\Exception\NotFoundException If the email was invalid
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->login()`)
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
	 * Ensures that the rate limit was not exceeded
	 *
	 * @throws \Kirby\Exception\PermissionException If the rate limit was exceeded
	 */
	protected function checkRateLimit(string $email): void
	{
		// check for blocked ips
		if ($this->isBlocked($email) === true) {
			$this->kirby->trigger('user.login:failed', compact('email'));

			throw new PermissionException(
				details: ['reason' => 'rate-limited'],
				fallback: 'Rate limit exceeded'
			);
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
			$this->checkRateLimit($email);

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
			$details = $e instanceof Exception ? $e->getDetails() : [];

			// log invalid login trial unless the rate limit is already active
			if (($details['reason'] ?? null) !== 'rate-limited') {
				try {
					$this->track($email);
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
	 * Returns the absolute path to the logins log
	 */
	public function logfile(): string
	{
		return $this->kirby->root('accounts') . '/.logins';
	}

	/**
	 * Read all tracked logins
	 */
	public function log(): array
	{
		try {
			$log  = Data::read($this->logfile(), 'json');
			$read = true;
		} catch (Throwable) {
			$log  = [];
			$read = false;
		}

		// ensure that the category arrays are defined
		$log['by-ip']    ??= [];
		$log['by-email'] ??= [];

		// remove all elements on the top level with different keys (old structure)
		$log = array_intersect_key($log, array_flip(['by-ip', 'by-email']));

		// remove entries that are no longer needed
		$originalLog = $log;
		$time        = time() - $this->kirby->option('auth.timeout', 3600);

		foreach ($log as $category => $entries) {
			$log[$category] = array_filter(
				$entries,
				fn ($entry) => $entry['time'] > $time
			);
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
	 * Clears the cached user data after logout
	 */
	public function flush(): void
	{
		$this->impersonate = null;
		$this->status      = null;
		$this->user        = null;
	}

	/**
	 * Tracks a login
	 *
	 * @param bool $triggerHook If `false`, no user.login:failed hook is triggered
	 */
	public function track(
		string|null $email,
		bool $triggerHook = true
	): bool {
		if ($triggerHook === true) {
			$this->kirby->trigger('user.login:failed', compact('email'));
		}

		$ip   = $this->kirby->visitor()->ip(hash: true);
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
			$this->checkRateLimit($email);

			if (
				isset(static::$challenges[$challenge]) === true &&
				class_exists(static::$challenges[$challenge]) === true &&
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
				'Invalid authentication challenge: ' . $challenge
			);
		} catch (Throwable $e) {
			$details = $e instanceof Exception ? $e->getDetails() : [];

			if (
				empty($email) === false &&
				($details['reason'] ?? null) !== 'rate-limited'
			) {
				$this->track($email);
			}

			// sleep for a random amount of milliseconds
			// to make automated attacks harder and to
			// avoid leaking whether the user exists
			usleep(random_int(10000, 2000000));

			// specifically copy over the marker for a destroyed challenge
			// even in production (used by the Panel to reset to the login form)
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
}
