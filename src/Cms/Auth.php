<?php

namespace Kirby\Cms;

use Kirby\Auth\Challenges;
use Kirby\Auth\Csrf;
use Kirby\Auth\Exception\ChallengeTimeoutException;
use Kirby\Auth\Exception\InvalidChallengeCodeException;
use Kirby\Auth\Exception\LoginNotPermittedException;
use Kirby\Auth\Exception\NoAvailableChallengeException;
use Kirby\Auth\Exception\RateLimitException;
use Kirby\Auth\Exception\UserNotFoundException;
use Kirby\Auth\Limits;
use Kirby\Auth\Methods;
use Kirby\Auth\User as AuthUser;
use Kirby\Auth\Status;
use Kirby\Exception\Exception;
use Kirby\Http\Idn;
use Kirby\Http\Request\Auth\BasicAuth;
use Kirby\Session\Session;
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
	 * Available auth challenge and methods classes
	 * from the core and plugins
	 */
	public static array $challenges = [];
	public static array $methods = [];

	protected Csrf $csrf;
	protected Limits $limits;
	protected Challenges $challenge;
	protected Methods $method;

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
		$this->csrf      = new Csrf($kirby);
		$this->limits    = new Limits($kirby);
		$this->challenge = new Challenges($kirby);
		$this->method    = new Methods($kirby);
		$this->user      = new AuthUser($this, $kirby);
	}

	/**
	 * Creates an authentication challenge (one-time auth code)
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
		$email   = Idn::decodeEmail($email);
		$session = $this->kirby->session([
			'createMode' => 'cookie',
			'long'       => $long === true
		]);

		// catch every exception to hide them from attackers
		// unless auth debugging is enabled
		try {
			// create available challenge for that user
			$challenge = $this->challenge->create($session, $email, $mode, );

			if ($challenge === null) {
				throw new NoAvailableChallengeException();
			}

		} catch (Throwable $e) {
			if ($e instanceof UserNotFoundException) {
				$this->kirby->trigger('user.login:failed', ['email' => $email]);
			}

			// only throw the exception in auth debug mode
			$this->fail($e);
		}

		// always set the email, mode and timeout, even if the challenge
		// won't be created; this avoids leaking whether the user exists
		$timeout = $this->challenge->timeout();
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
		return $this->challenge->enabled();
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
	 * @deprecated 6.0.0 Use `self::limits()->isBlocked()` instead
	 */
	public function isBlocked(string $email): bool
	{
		return $this->limits->isBlocked($email);
	}

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
		$user = $this->method->attempt('password', $email, $password, $long, 'login');

		if ($user instanceof User === false) {
			// if a method returned a pending status here,
			// it's a misconfiguration (e.g. password + 2FA active);
			// keep the existing login signature strict
			throw new LoginNotPermittedException();
		}

		$this->setUser($user);
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
		// explicit 2FA flow: password validation first, then a challenge
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
	 * Sets a user object as the current user in the cache
	 * @internal
	 */
	public function setUser(User $user): void
	{
		// clear the status cache
		$this->status = null;
		$this->user->set($user);
	}

	public function normalizeSession(Session|array|null $session): Session
	{
		if (is_array($session) === true) {
			return $this->kirby->session($session);
		}

		if ($session instanceof Session === false) {
			return  $this->kirby->session(['detect' => true]);
		}

		return $session;
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
			$this->status !== null &&
			$session === null &&
			$allowImpersonation === true
		) {
			return $this->status;
		}

		$session      = $this->normalizeSession($session);
		$user         = $this->user($session, $allowImpersonation);
		$impersonated = match ($allowImpersonation) {
			true  => $this->user->isImpersonated(),
			false => false
		};

		$status = Status::for(
			kirby:        $this->kirby,
			user:         $user,
			impersonated: $impersonated,
			session:      $session,
			challenges:   $this->challenge->enabled()
		);

		// only cache the default object
		if ($session === null && $allowImpersonation === true) {
			return $this->status = $status;
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
		if ($triggerHook === true) {
			$this->kirby->trigger('user.login:failed', ['email' => $email]);
		}

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
			$this->limits->ensure($email);

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
					$this->kirby->trigger('user.login:failed', ['email' => $email]);
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
			$session   = $this->kirby->session();
			$challenge = $this->challenge->verify($session, $code);
			$user      = $challenge->user();

			$this->logout();
			$user->loginPasswordless();

			// allow the user to set a new password
			// without knowing the previous one
			if ($challenge->mode() === 'password-reset') {
				$session->set('kirby.resetPassword', true);
			}

			$this->setUser($user);
			return $user;

		} catch (Throwable $e) {
			$details = $e instanceof Exception ? $e->getDetails() : [];

			if (
				empty($email) === false &&
				$e instanceof RateLimitException === false
			) {
				$this->kirby->trigger('user.login:failed', ['email' => $email]);
				$this->limits->track($email);
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
