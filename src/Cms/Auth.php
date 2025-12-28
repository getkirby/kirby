<?php

namespace Kirby\Cms;

use Kirby\Auth\Challenge;
use Kirby\Auth\Csrf;
use Kirby\Auth\Exception\ChallengeTimeoutException;
use Kirby\Auth\Exception\InvalidChallengeCodeException;
use Kirby\Auth\Exception\LoginNotPermittedException;
use Kirby\Auth\Exception\NoAvailableChallengeException;
use Kirby\Auth\Exception\RateLimitException;
use Kirby\Auth\Exception\UserNotFoundException;
use Kirby\Auth\User as AuthUser;
use Kirby\Cms\Auth\Status;
use Kirby\Data\Data;
use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
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

	protected Csrf $csrf;

	/**
	 * Cache of the auth status object
	*/
	protected Status|null $status = null;

	protected AuthUser $user;

	/**
	 * @codeCoverageIgnore
	 */
	public function __construct(
		protected App $kirby
	) {
		$this->csrf = new Csrf($kirby);
		$this->user = new AuthUser($this, $kirby);
	}

	/**
	 * Ensures that the rate limit was not exceeded
	 *
	 * @throws \Kirby\Auth\Exception\RateLimitException
	 */
	protected function checkRateLimit(string $email): void
	{
		// check for blocked ips
		if ($this->isBlocked($email) === true) {
			$this->kirby->trigger('user.login:failed', compact('email'));
			throw new RateLimitException();
		}
	}

	/**
	 * Creates an authentication challenge
	 * (one-time auth code)
	 * @since 3.5.0
	 *
	 * @param bool $long If `true`, a long session will be created
	 * @param 'login'|'password-reset'|'2fa' $mode Purpose of the code
	 *
	 * @throws \Kirby\Auth\Exception\NoAvailableChallengeException (only in debug mode)
	 * @throws \Kirby\Auth\Exception\UserNotFoundException (only in debug mode)
	 * @throws \Kirby\Auth\Exception\RateLimitException
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
				throw new UserNotFoundException(name: $email);
			}

			// try to find an enabled challenge that is available for that user
			foreach ($this->enabledChallenges() as $type) {
				if ($challenge = Challenge::for($type, $user, $mode)) {
					$code = $challenge->create();
					$session->set('kirby.challenge.type', $challenge->type());

					if ($code !== null) {
						$session->set(
							'kirby.challenge.code',
							password_hash($code, PASSWORD_DEFAULT)
						);
					}
				}
			}

			// if no suitable challenge was found
			if (($challenge ?? null) === null) {
				throw new NoAvailableChallengeException();
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
	 * Returns the logged in user from basic authentication header
	 *
	 * @throws \Kirby\Auth\Exception\InvalidAuthHeaderException
	 * @throws \Kirby\Exception\PermissionException if basic authentication is not allowed
	 */
	public function currentUserFromBasicAuth(
		BasicAuth|null $auth = null
	): User|null {
		return $this->user->currentFromBasicAuth($auth);
	}

	/**
	 * Returns the currently impersonated user
	 */
	public function currentUserFromImpersonation(): User|null
	{
		return $this->user->currentFromImpersonation();
	}

	/**
	 * Returns the logged in user from the session
	 */
	public function currentUserFromSession(
		Session|array|null $session = null
	): User|null {
		return $this->user->currentFromSession($session);
	}

	/**
	 * Returns the list of enabled challenges in the configured order
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
		$this->status = null;
		$this->user->flush();
	}

	/**
	 * Become any existing user or disable the current user
	 *
	 * @param string|null $who User ID or email address,
	 *                         `null` to use the actual user again,
	 *                         `'kirby'` for a virtual admin user or
	 *                         `'nobody'` to disable the actual user
	 *
	 * @throws \Kirby\Auth\Exception\UserNotFoundException
	 */
	public function impersonate(string|null $who = null): User|null
	{
		// clear the status cache
		$this->status = null;
		return $this->user->impersonate($who);
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
	 * Returns the absolute path to the logins log
	 */
	public function logfile(): string
	{
		return $this->kirby->root('accounts') . '/.logins';
	}

	/**
	 * Login a user by email and password
	 *
	 * @throws \Kirby\Auth\Exception\RateLimitException
	 * @throws \Kirby\Auth\Exception\UserNotFoundException
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->login()`)
	 * @throws \Kirby\Auth\Exception\LoginNotPermittedException If any other error occurred with debug mode off
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
	 * @throws \Kirby\Auth\Exception\RateLimitException
	 * @throws \Kirby\Auth\Exception\UserNotFoundException
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->login()`)
	 * @throws \Kirby\Auth\Exception\LoginNotPermittedException If any other error occurred with debug mode off
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
		$this->user->impersonate(null);

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
		// clear the status cache
		$this->status = null;
		$this->user->set($user);
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
					$this->user->currentFromImpersonation() !== null  => 'impersonated',
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

		if ($allowImpersonation === true && $this->user->isImpersonated()) {
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
		return $this->user->user($session, $allowImpersonation);
	}

	/**
	 * Validates the user credentials and returns the user object on success;
	 * otherwise logs the failed attempt
	 *
	 * @throws \Kirby\Auth\Exception\RateLimitException
	 * @throws \Kirby\Auth\Exception\UserNotFoundException
	 * @throws \Kirby\Exception\InvalidArgumentException If the password is not valid (via `$user->validatePassword()`)
	 * @throws \Kirby\Auth\Exception\LoginNotPermittedException If any other error occurred with debug mode off
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
			$user = $this->kirby->users()->find($email);

			if ($user?->validatePassword($password) !== true) {
				throw new UserNotFoundException(name: $email);
			}

			return $user;

		} catch (Throwable $e) {
			// log invalid login trial unless the rate limit is already active
			if ($e instanceof RateLimitException === false) {
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
			$this->fail($e, new LoginNotPermittedException());
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
	 * @throws \Kirby\Auth\Exception\RateLimitException
	 * @throws \Kirby\Auth\Exception\UserNotFoundException
	 * @throws \Kirby\Exception\InvalidArgumentException If no authentication challenge is active
	 * @throws \Kirby\Exception\LogicException If the authentication challenge is invalid
	 * @throws \Kirby\Auth\Exception\LoginNotPermittedException If any other error occurred with debug mode off
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
				throw new ChallengeTimeoutException();
			}

			// check if we have an active challenge
			$email = $session->get('kirby.challenge.email');
			$type  = $session->get('kirby.challenge.type');

			if (is_string($email) !== true || is_string($type) !== true) {
				// if the challenge timed out on the previous request, the
				// challenge data was already deleted from the session, so we can
				// set `challengeDestroyed` to `true` in this response as well;
				// however we must only base this on the email, not the type
				// (otherwise "faked" challenges would be leaked)
				throw new InvalidArgumentException(
					details: ['challengeDestroyed' => is_string($email) !== true],
					fallback: 'No authentication challenge is active'
				);
			}

			$user = App::instance()->users()->find($email);

			if ($user === null) {
				throw new UserNotFoundException(name: $email);
			}

			// rate-limiting
			$this->checkRateLimit($email);

			if ($challenge = Challenge::from($session)) {
				if ($challenge->verify($code) !== true) {
					throw new InvalidChallengeCodeException();
				}

				$this->logout();
				$challenge->user()->loginPasswordless();

				// allow the user to set a new password
				// without knowing the previous one
				if ($challenge->mode() === 'password-reset') {
					$session->set('kirby.resetPassword', true);
				}

				// clear the status cache
				$this->status = null;

				return $challenge->user();
			}

			throw new LogicException(
				message: 'Invalid authentication challenge: ' . $type
			);

		} catch (Throwable $e) {
			$details = $e instanceof Exception ? $e->getDetails() : [];

			if (
				empty($email) === false &&
				$e instanceof RateLimitException === false
			) {
				$this->track($email);
			}

			if ($e instanceof ChallengeTimeoutException) {
				$this->logout();
			}

			// sleep for a random amount of milliseconds
			// to make automated attacks harder and to
			// avoid leaking whether the user exists
			usleep(random_int(10000, 2000000));

			// specifically copy over the marker for a destroyed challenge
			// even in production (used by the Panel to reset to the login form)
			$fallback = new InvalidChallengeCodeException(
				details: [
					'challengeDestroyed' => $details['challengeDestroyed'] ?? false
				],
			);

			// keep throwing the original error in debug mode,
			// otherwise hide it to avoid leaking security-relevant information
			$this->fail($e, $fallback);
		}
	}
}
