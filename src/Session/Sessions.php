<?php

namespace Kirby\Session;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Http\Cookie;
use Kirby\Http\Request;
use Kirby\Toolkit\Str;
use Throwable;

/**
 * Sessions - Base class for all session fiddling
 *
 * @package   Kirby Session
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Sessions
{
	protected SessionStore $store;
	protected string $mode;
	protected string $cookieName;

	protected array $cache = [];

	/**
	 * Creates a new Sessions instance
	 *
	 * @param \Kirby\Session\SessionStore|string $store SessionStore object or a path to the storage directory (uses the FileSessionStore)
	 * @param array $options Optional additional options:
	 *                       - `mode`: Default token transmission mode (cookie, header or manual); defaults to `cookie`
	 *                       - `cookieName`: Name to use for the session cookie; defaults to `kirby_session`
	 *                       - `gcInterval`: How often should the garbage collector be run?; integer or `false` for never; defaults to `100`
	 */
	public function __construct(
		SessionStore|string $store,
		array $options = []
	) {
		$this->store = match (true) {
			$store instanceof SessionStore => $store,
			default                        => new FileSessionStore($store),
		};

		$this->mode       = $options['mode']       ?? 'cookie';
		$this->cookieName = $options['cookieName'] ?? 'kirby_session';
		$gcInterval       = $options['gcInterval'] ?? 100;

		// validate options
		if (in_array($this->mode, ['cookie', 'header', 'manual'], true) === false) {
			throw new InvalidArgumentException(
				data: [
					'method'   => 'Sessions::__construct',
					'argument' => '$options[\'mode\']'
				],
				translate: false
			);
		}

		// trigger automatic garbage collection with the given probability
		if (is_int($gcInterval) === true && $gcInterval > 0) {
			// convert the interval into a probability between 0 and 1
			$gcProbability = 1 / $gcInterval;

			// generate a random number
			$random = mt_rand(1, 10000);

			// $random will be below or equal $gcProbability * 10000 with a probability of $gcProbability
			if ($random <= $gcProbability * 10000) {
				$this->collectGarbage();
			}
		} elseif ($gcInterval !== false) {
			throw new InvalidArgumentException(
				data: [
					'method'   => 'Sessions::__construct',
					'argument' => '$options[\'gcInterval\']'
				],
				translate: false
			);
		}
	}

	/**
	 * Creates a new empty session
	 *
	 * @param array $options Optional additional options:
	 *                       - `mode`: Token transmission mode (cookie or manual); defaults to default mode of the Sessions instance
	 *                       - `startTime`:  Time the session starts being valid (date string or timestamp); defaults to `now`
	 *                       - `expiryTime`: Time the session expires (date string or timestamp); defaults to `+ 2 hours`
	 *                       - `timeout`: Activity timeout in seconds (integer or false for none); defaults to `1800` (half an hour)
	 *                       - `renewable`: Should it be possible to extend the expiry date?; defaults to `true`
	 */
	public function create(array $options = []): Session
	{
		// fall back to default mode
		$options['mode'] ??= $this->mode;

		return new Session($this, null, $options);
	}

	/**
	 * Returns the specified Session object
	 *
	 * @param string $token Session token, either including or without the key
	 * @param string|null $mode Optional transmission mode override
	 */
	public function get(string $token, string|null $mode = null): Session
	{
		return $this->cache[$token] ??= new Session(
			$this,
			$token,
			['mode' => $mode ?? $this->mode]
		);
	}

	/**
	 * Returns the current session based on the configured token transmission mode:
	 * - In `cookie` mode: Gets the session from the cookie
	 * - In `header` mode: Gets the session from the `Authorization` request header
	 * - In `manual` mode: Fails and throws an Exception
	 *
	 * @return \Kirby\Session\Session|null Either the current session or null in case there isn't one
	 * @throws \Kirby\Exception\Exception
	 * @throws \Kirby\Exception\LogicException
	 */
	public function current(): Session|null
	{
		$token = match ($this->mode) {
			'cookie' => $this->tokenFromCookie(),
			'header' => $this->tokenFromHeader(),
			'manual' => throw new LogicException(
				key: 'session.sessions.manualMode',
				fallback: 'Cannot automatically get current session in manual mode',
				translate: false,
				httpCode: 500
			),
			// unexpected error that shouldn't occur
			default => throw new Exception(translate: false) // @codeCoverageIgnore
		};

		// no token was found, no session
		if (is_string($token) === false) {
			return null;
		}

		// token was found, try to get the session
		try {
			return $this->get($token);
		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * Returns the current session using the following detection order without using the configured mode:
	 * - Tries to get the session from the `Authorization` request header
	 * - Tries to get the session from the cookie
	 * - Otherwise returns null
	 *
	 * @return \Kirby\Session\Session|null Either the current session or null in case there isn't one
	 */
	public function currentDetected(): Session|null
	{
		$header = $this->tokenFromHeader();
		$cookie = $this->tokenFromCookie();

		// prefer header token over cookie token
		$token = $header ?? $cookie;

		// no token was found, no session
		if (is_string($token) === false) {
			return null;
		}

		// token was found, try to get the session
		try {
			return $this->get($token, match (true) {
				$header !== null => 'header',
				$cookie !== null => 'cookie'
			});
		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * Getter for the session store instance
	 */
	public function store(): SessionStore
	{
		return $this->store;
	}

	/**
	 * Getter for the cookie name
	 */
	public function cookieName(): string
	{
		return $this->cookieName;
	}

	/**
	 * Deletes all expired sessions
	 *
	 * If the `gcInterval` is configured, this is done automatically
	 * on init of the Sessions object.
	 */
	public function collectGarbage(): void
	{
		$this->store()->collectGarbage();
	}

	/**
	 * Updates the instance cache with a newly created
	 * session or a session with a regenerated token
	 *
	 * @internal
	 * @param \Kirby\Session\Session $session Session instance to push to the cache
	 */
	public function updateCache(Session $session): void
	{
		$this->cache[$session->token()] = $session;
	}

	/**
	 * Returns the auth token from the cookie
	 */
	protected function tokenFromCookie(): string|null
	{
		$value = Cookie::get($this->cookieName());

		if (is_string($value) === false) {
			return null;
		}

		return $value;
	}

	/**
	 * Returns the auth token from the Authorization header
	 */
	protected function tokenFromHeader(): string|null
	{
		$request = new Request();
		$headers = $request->headers();

		// check if the header exists at all
		if ($header = $headers['Authorization'] ?? null) {
			// check if the header uses the "Session" scheme
			if (Str::startsWith($header, 'Session ', true) !== true) {
				return null;
			}

			// return the part after the scheme
			return substr($header, 8);
		}

		return null;
	}
}
