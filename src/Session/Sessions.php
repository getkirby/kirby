<?php

namespace Kirby\Session;

use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Throwable;

/**
 * The main session handler for automatic and manual sessions
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Sessions
{
	public const MODES = ['cookie', 'header', 'manual'];
	public const DURATION_NORMAL = 7200;
	public const DURATION_LONG = 1209600;
	public const TIMEOUT = 1800;

	/**
	 * @var array<string, \Kirby\Session\Session>
	 */
	protected array $cache = [];
	protected Session|null $autoSession = null;
	protected Header|null $header = null;

	/**
	 * @param 'cookie'|'header'|'manual' $mode
	 */
	public function __construct(
		protected Store $store,
		protected string $mode = 'cookie',
		protected Cookie|null $cookie = null,
		protected int $durationNormal = self::DURATION_NORMAL,
		protected int $durationLong = self::DURATION_LONG,
		protected int|false $timeout = self::TIMEOUT,
	) {
		if (in_array($mode, self::MODES, true) === false) {
			throw new InvalidArgumentException(
				data: [
					'method'   => 'Sessions::__construct',
					'argument' => '$mode'
				],
				translate: false
			);
		}
	}

	/**
	 * Deletes all expired sessions
	 *
	 * If the `gcInterval` is configured, this is done automatically
	 * with the configured probability when the Sessions object is
	 * created via `::factory()`.
	 */
	public function collectGarbage(): void
	{
		$this->store()->collectGarbage();
	}

	/**
	 * Returns the session cookie instance
	 * @since 6.0.0
	 */
	public function cookie(): Cookie
	{
		return $this->cookie ??= new Cookie();
	}

	/**
	 * Getter for the cookie domain
	 * @deprecated 6.0.0 Use `::cookie()->domain()` instead.
	 */
	public function cookieDomain(): string|null
	{
		return $this->cookie()->domain();
	}

	/**
	 * Getter for the cookie name
	 * @deprecated 6.0.0 Use `::cookie()->name()` instead.
	 */
	public function cookieName(): string
	{
		return $this->cookie()->name();
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
	 * Creates a new empty session that is transmitted manually
	 * @since 3.3.1
	 * @deprecated 6.0.0 Use `::create()` with `['mode' => 'manual']` instead.
	 */
	public function createManually(array $options = []): Session
	{
		$options['mode'] = 'manual';

		return $this->create($options);
	}

	/**
	 * Returns the current session based on the
	 * configured token transmission $mode
	 *
	 * @throws \Kirby\Exception\LogicException In `manual` mode
	 */
	public function current(): Session|null
	{
		$token = match ($this->mode) {
			'cookie' => $this->cookie()->get(),
			'header' => $this->header()->get(),
			'manual' => throw new LogicException(
				key: 'session.sessions.manualMode',
				fallback: 'Cannot automatically get current session in manual mode',
				translate: false,
				httpCode: 500
			)
		};

		try {
			if ($token !== null) {
				return $this->find($token);
			}

			return null;

		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * Returns the current session by auto-detecting the transmission mode
	 * @deprecated 6.0.0 Use `::detect()` instead.
	 */
	public function currentDetected(): Session|null
	{
		return $this->detect();
	}

	/**
	 * Detects the current session from the request, ignoring the
	 * configured mode: prefers a token from the `Authorization` header
	 * and otherwise falls back to the cookie.
	 *
	 * @since 6.0.0
	 */
	public function detect(): Session|null
	{
		try {
			$token = $this->header()->get();

			if ($token !== null) {
				return $this->find($token, 'header');
			}

			$token = $this->cookie()->get();

			if ($token !== null) {
				return $this->find($token, 'cookie');
			}

			return null;

		} catch (Throwable) {
			return null;
		}
	}

	/**
	 * Creates a new Sessions instance from a loose options array
	 *
	 * @param \Kirby\Session\Store|string $store Store object or a path to the storage directory (uses the FileStore)
	 * @param array $options Optional additional options:
	 *                       - `mode`: Default token transmission mode (cookie, header or manual); defaults to `cookie`
	 *                       - `cookieDomain`: Domain to set the cookie to (this disables the cookie path restriction); defaults to none (default browser behavior)
	 *                       - `cookieName`: Name to use for the session cookie; defaults to `kirby_session`
	 *                       - `gcInterval`: How often should the garbage collector be run?; integer or `false` for never; defaults to `100`
	 *                       - `durationNormal`: Duration of normal sessions in seconds; defaults to 2 hours
	 *                       - `durationLong`: Duration of "remember me" sessions in seconds; defaults to 2 weeks
	 *                       - `timeout`: Activity timeout in seconds (integer or false for none); *only* used for normal sessions; defaults to `1800` (half an hour)
	 */
	public static function factory(
		Store|string $store,
		array $options = []
	): static {
		$cookie = new Cookie(
			name:   $options['cookieName']   ?? 'kirby_session',
			domain: $options['cookieDomain'] ?? null
		);

		if ($store instanceof Store === false) {
			$store = new FileStore($store);
		}

		$sessions = new static(
			store:          $store,
			mode:           $options['mode'] ?? 'cookie',
			cookie:         $cookie,
			durationNormal: $options['durationNormal'] ?? self::DURATION_NORMAL,
			durationLong:   $options['durationLong']   ?? self::DURATION_LONG,
			timeout:        $options['timeout']        ?? self::TIMEOUT,
		);

		// run garbage collection on average once every $gcInterval requests
		$gcInterval = $options['gcInterval'] ?? 100;

		if ($gcInterval !== false) {
			if (is_int($gcInterval) === false || $gcInterval < 1) {
				throw new InvalidArgumentException(
					data: [
						'method'   => 'Sessions::factory',
						'argument' => '$options[\'gcInterval\']'
					],
					translate: false
				);
			}

			if (mt_rand(1, $gcInterval) === 1) {
				$sessions->collectGarbage();
			}
		}

		return $sessions;
	}

	/**
	 * Returns the session for the given token
	 *
	 * @param string $token Session token, either including or without the key
	 * @param string|null $mode Optional transmission mode override
	 *
	 * @throws \Kirby\Exception\InvalidArgumentException If the token is malformed
	 * @throws \Kirby\Exception\NotFoundException If no matching session exists
	 * @throws \Kirby\Exception\LogicException If the session data is invalid or expired
	 */
	public function find(string $token, string|null $mode = null): Session
	{
		return $this->cache[$token] ??= new Session(
			$this,
			$token,
			['mode' => $mode ?? $this->mode]
		);
	}

	/**
	 * Returns the automatic session for the current request,
	 * creating a new one on demand if none exists yet
	 *
	 * @param array $options Optional additional options:
	 *                       - `detect`: Whether to allow sessions in the `Authorization` HTTP header (`true`) or only in the session cookie (`false`); defaults to `false`
	 *                       - `createMode`: When creating a new session, should it be set as a cookie or is it going to be transmitted manually to be used in a header?; defaults to `cookie`
	 *                       - `long`: Whether the session is a long "remember me" session or a normal session; defaults to `false`
	 */
	public function get(array $options = []): Session
	{
		// merge options with defaults
		$options = [
			'detect'     => false,
			'createMode' => 'cookie',
			'long'       => false,
			...$options
		];

		// determine expiry options based on the session type
		if ($options['long'] === true) {
			$duration = $this->durationLong;
			$timeout  = false;
		} else {
			$duration = $this->durationNormal;
			$timeout  = $this->timeout;
		}

		// get the current session
		$session = match ($options['detect']) {
			true    => $this->detect(),
			default => $this->current()
		};

		// create a new session
		if ($session === null) {
			$now = time();

			// memoize the created session so that repeated calls
			// within the same request don't create multiple
			$session = $this->autoSession ??= $this->create([
				'mode'       => $options['createMode'],
				'startTime'  => $now,
				'expiryTime' => $now + $duration,
				'timeout'    => $timeout,
				'renewable'  => true,
			]);
		}

		// update the session configuration if the $options changed
		// always use the less strict value for compatibility with features
		// that depend on the less strict behavior
		if ($duration > $session->duration()) {
			// the duration needs to be extended
			$session->duration($duration);
		}

		if ($session->timeout() !== false) {
			// a timeout exists
			if ($timeout === false) {
				// it needs to be completely disabled
				$session->timeout(false);
			} elseif ($timeout > $session->timeout()) {
				// it needs to be extended
				$session->timeout($timeout);
			}
		}

		// if the session has been created and was not yet initialized,
		// update the mode to a custom mode;
		// don't update back to cookie mode because the
		// "special" behavior always wins
		if (
			$session->token() === null &&
			$options['createMode'] !== 'cookie'
		) {
			$session->mode($options['createMode']);
		}

		return $session;
	}

	/**
	 * Returns the session for the given token in manual transmission mode
	 * @since 3.3.1
	 * @deprecated 6.0.0 Use `::find()` instead.
	 */
	public function getManually(string $token): Session
	{
		return $this->find($token, 'manual');
	}

	/**
	 * Returns the session header instance
	 * @since 6.0.0
	 */
	public function header(): Header
	{
		return $this->header ??= new Header();
	}

	/**
	 * Getter for the session store instance
	 */
	public function store(): Store
	{
		return $this->store;
	}

	/**
	 * Tracks a newly created session or a session with a
	 * regenerated token in the instance cache (keyed by its
	 * current token) so that `::find()` keeps returning the
	 * same instance after the token changed
	 *
	 * @since 6.0.0
	 * @internal
	 */
	public function update(Session $session): void
	{
		$this->cache[$session->token()] = $session;
	}

	/**
	 * Updates the instance cache with a newly created
	 * session or a session with a regenerated token
	 *
	 * @deprecated 6.0.0 Use `::update()` instead.
	 */
	public function updateCache(Session $session): void
	{
		$this->update($session);
	}
}
