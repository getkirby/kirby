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
	protected $store;
	protected $mode;
	protected $cookieName;

	protected $cache = [];

	/**
	 * Creates a new Sessions instance
	 *
	 * @param \Kirby\Session\SessionStore|string $store SessionStore object or a path to the storage directory (uses the FileSessionStore)
	 * @param array $options Optional additional options:
	 *                       - `mode`: Default token transmission mode (cookie, header or manual); defaults to `cookie`
	 *                       - `cookieName`: Name to use for the session cookie; defaults to `kirby_session`
	 *                       - `gcInterval`: How often should the garbage collector be run?; integer or `false` for never; defaults to `100`
	 */
	public function __construct($store, array $options = [])
	{
		if (is_string($store)) {
			$this->store = new FileSessionStore($store);
		} elseif ($store instanceof SessionStore) {
			$this->store = $store;
		} else {
			throw new InvalidArgumentException([
				'data'      => ['method' => 'Sessions::__construct', 'argument' => 'store'],
				'translate' => false
			]);
		}

		$this->mode       = $options['mode']       ?? 'cookie';
		$this->cookieName = $options['cookieName'] ?? 'kirby_session';
		$gcInterval       = $options['gcInterval'] ?? 100;

		// validate options
		if (!in_array($this->mode, ['cookie', 'header', 'manual'])) {
			throw new InvalidArgumentException([
				'data'      => ['method' => 'Sessions::__construct', 'argument' => '$options[\'mode\']'],
				'translate' => false
			]);
		}
		if (!is_string($this->cookieName)) {
			throw new InvalidArgumentException([
				'data'      => ['method' => 'Sessions::__construct', 'argument' => '$options[\'cookieName\']'],
				'translate' => false
			]);
		}

		// trigger automatic garbage collection with the given probability
		if (is_int($gcInterval) && $gcInterval > 0) {
			// convert the interval into a probability between 0 and 1
			$gcProbability = 1 / $gcInterval;

			// generate a random number
			$random = mt_rand(1, 10000);

			// $random will be below or equal $gcProbability * 10000 with a probability of $gcProbability
			if ($random <= $gcProbability * 10000) {
				$this->collectGarbage();
			}
		} elseif ($gcInterval !== false) {
			throw new InvalidArgumentException([
				'data'      => ['method' => 'Sessions::__construct', 'argument' => '$options[\'gcInterval\']'],
				'translate' => false
			]);
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
	 * @return \Kirby\Session\Session
	 */
	public function create(array $options = [])
	{
		// fall back to default mode
		if (!isset($options['mode'])) {
			$options['mode'] = $this->mode;
		}

		return new Session($this, null, $options);
	}

	/**
	 * Returns the specified Session object
	 *
	 * @param string $token Session token, either including or without the key
	 * @param string $mode Optional transmission mode override
	 * @return \Kirby\Session\Session
	 */
	public function get(string $token, string $mode = null)
	{
		if (isset($this->cache[$token])) {
			return $this->cache[$token];
		}

		return $this->cache[$token] = new Session($this, $token, ['mode' => $mode ?? $this->mode]);
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
	public function current()
	{
		$token = match ($this->mode) {
			'cookie' => $this->tokenFromCookie(),
			'header' => $this->tokenFromHeader(),
			'manual' => throw new LogicException([
				'key'       => 'session.sessions.manualMode',
				'fallback'  => 'Cannot automatically get current session in manual mode',
				'translate' => false,
				'httpCode'  => 500
			]),
			// unexpected error that shouldn't occur
			default => throw new Exception(['translate' => false]) // @codeCoverageIgnore
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
	public function currentDetected()
	{
		$tokenFromHeader = $this->tokenFromHeader();
		$tokenFromCookie = $this->tokenFromCookie();

		// prefer header token over cookie token
		$token = $tokenFromHeader ?? $tokenFromCookie;

		// no token was found, no session
		if (!is_string($token)) {
			return null;
		}

		// token was found, try to get the session
		try {
			$mode = (is_string($tokenFromHeader)) ? 'header' : 'cookie';
			return $this->get($token, $mode);
		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * Getter for the session store instance
	 * Used internally
	 *
	 * @return \Kirby\Session\SessionStore
	 */
	public function store()
	{
		return $this->store;
	}

	/**
	 * Getter for the cookie name
	 * Used internally
	 *
	 * @return string
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
	 *
	 * @return void
	 */
	public function collectGarbage()
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
	public function updateCache(Session $session)
	{
		$this->cache[$session->token()] = $session;
	}

	/**
	 * Returns the auth token from the cookie
	 *
	 * @return string|null
	 */
	protected function tokenFromCookie()
	{
		$value = Cookie::get($this->cookieName());

		if (is_string($value) === false) {
			return null;
		}

		return $value;
	}

	/**
	 * Returns the auth token from the Authorization header
	 *
	 * @return string|null
	 */
	protected function tokenFromHeader()
	{
		$request = new Request();
		$headers = $request->headers();

		// check if the header exists at all
		if (isset($headers['Authorization']) === false) {
			return null;
		}

		// check if the header uses the "Session" scheme
		$header = $headers['Authorization'];
		if (Str::startsWith($header, 'Session ', true) !== true) {
			return null;
		}

		// return the part after the scheme
		return substr($header, 8);
	}
}
