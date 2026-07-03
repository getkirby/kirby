<?php

namespace Kirby\Session;

use Kirby\Exception\Exception;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Toolkit\Str;

/**
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class Session
{
	// parent data
	protected string $mode;

	// session token
	protected Token|null $token = null;

	// persistent data
	protected int $startTime;
	protected int $expiryTime;
	protected int $duration;
	protected int|false $timeout = false;
	protected int|null $lastActivity = null;
	protected bool $renewable;
	protected Data $data;
	protected array|null $newSession = null;

	// temporary state flags
	protected bool $updatingLastActivity = false;
	protected bool $destroyed = false;
	protected bool $writeMode = false;
	protected bool $needsRetransmission = false;

	/**
	 * Creates a new Session instance
	 *
	 * @param \Kirby\Session\Sessions $sessions Parent sessions object
	 * @param string|null $token Session token or null for a new session
	 * @param array $options Optional additional options:
	 *                       - `mode`: Token transmission mode (cookie or manual); defaults to `cookie`
	 *                       - `startTime`: Time the session starts being valid (date string or timestamp); defaults to `now`
	 *                       - `expiryTime`: Time the session expires (date string or timestamp); defaults to `+ 2 hours`
	 *                       - `timeout`: Activity timeout in seconds (integer or false for none); defaults to `1800` (half an hour)
	 *                       - `renewable`: Should it be possible to extend the expiry date?; defaults to `true`
	 */
	public function __construct(
		protected Sessions $sessions,
		string|null $token,
		array $options
	) {
		$this->mode = $options['mode'] ?? 'cookie';

		// ensure that all changes are committed on script termination
		register_shutdown_function([$this, 'commit']);

		if (is_string($token) === true) {
			// existing session

			// set the token
			$this->token = Token::parse($token);

			// initialize, but only try to write to the session if not read-only
			// (only the case for moved sessions)
			$this->init();

			if ($this->token?->isReadonly() === false) {
				$this->autoRenew();
			}

			return;
		}

		// new session ($token = null)
		$now = time();

		// set data based on options
		$this->startTime  = static::timeToTimestamp($options['startTime'] ?? $now);
		$this->expiryTime = static::timeToTimestamp($options['expiryTime'] ?? '+ 2 hours', $this->startTime);
		$this->duration   = $this->expiryTime - $this->startTime;
		$this->timeout    = $options['timeout'] ?? 1800;
		$this->renewable  = $options['renewable'] ?? true;
		$this->data       = new Data($this, []);

		// validate persistent data
		if ($now > $this->expiryTime) {
			// session must not already be expired, but the start time may be in the future
			throw new InvalidArgumentException(
				data: [
					'method'   => 'Session::__construct',
					'argument' => '$options[\'expiryTime\']'
				],
				translate: false
			);
		}

		if ($this->duration < 0) {
			// expiry time must be after start time
			throw new InvalidArgumentException(
				data: [
					'method'   => 'Session::__construct',
					'argument' => '$options[\'startTime\' & \'expiryTime\']'
				],
				translate: false
			);
		}

		// set activity time if a timeout was requested
		if (is_int($this->timeout) === true) {
			$this->lastActivity = $now;
		}
	}

	/**
	 * Ensures that all pending changes are written
	 * to disk before the object is destructed
	 *
	 * @return void
	 */
	public function __destruct()
	{
		$this->commit();
	}

	/**
	 * Automatically renews the session if possible and necessary
	 */
	protected function autoRenew(): void
	{
		// check if the session needs renewal at all
		if ($this->needsRenewal() !== true) {
			return;
		}

		// re-load the session and check again to make sure that no other thread
		// already renewed the session in the meantime
		$this->prepareForWriting();

		if ($this->needsRenewal() === true) {
			$this->renew();
		}
	}

	/**
	 * @see \Kirby\Session\Data::clear()
	 * @since 6.0.0
	 */
	public function clear(): void
	{
		$this->data()->clear();
	}

	/**
	 * Writes all changes to the session to the session store
	 */
	public function commit(): void
	{
		// nothing to do if nothing changed or the session
		// has been just created or destroyed
		if (
			$this->writeMode !== true ||
			$this->token === null ||
			$this->destroyed === true
		) {
			return;
		}

		$token = $this->token;

		// collect all data
		if ($this->newSession !== null) {
			// the token has changed
			// we are writing to the old session:
			// it only gets the reference to the new session
			// and a shortened expiry time (30 second grace period)
			$data = [
				'startTime'  => $this->startTime(),
				'expiryTime' => time() + 30,
				'newSession' => $this->newSession[0]
			];

			// include the token key for the new session if we
			// have access to the PHP `sodium` extension;
			// otherwise (if no encryption is possible), the token key
			// is omitted, which makes the new session read-only
			// when accessed through the old session
			if ($crypto = $token->crypto()) {
				// encrypt the new token key with the old token key
				// so that attackers with read access to the session file
				// (e.g. via directory traversal) cannot impersonate the new session
				$data['newSessionKey'] = $crypto->encrypt($this->newSession[1]);
			}
		} else {
			$data = [
				'startTime'    => $this->startTime(),
				'expiryTime'   => $this->expiryTime(),
				'duration'     => $this->duration(),
				'timeout'      => $this->timeout(),
				'lastActivity' => $this->lastActivity,
				'renewable'    => $this->renewable(),
				'data'         => $this->data()->get()
			];
		}

		// encode the data and attach an HMAC
		$data = serialize($data);
		$data = hash_hmac('sha256', $data, $token->key) . "\n" . $data;

		// store the data
		$this->sessions->store()->set($token->expiry, $token->id, $data);
		$this->sessions->store()->unlock($token->expiry, $token->id);
		$this->writeMode = false;
	}

	/**
	 * Returns the session data object
	 */
	public function data(): Data
	{
		return $this->data;
	}

	/**
	 * @see \Kirby\Session\Data::decrement()
	 * @since 6.0.0
	 */
	public function decrement(
		string|array $key,
		int $by = 1,
		int|null $min = null
	): void {
		$this->data()->decrement($key, $by, $min);
	}

	/**
	 * Entirely destroys the session
	 */
	public function destroy(): void
	{
		// no need to destroy new or destroyed sessions
		if ($this->token === null || $this->destroyed === true) {
			return;
		}

		$token = $this->token;

		// remove session file
		$this->sessions->store()->destroy($token->expiry, $token->id);
		$this->destroyed           = true;
		$this->writeMode           = false;
		$this->needsRetransmission = false;

		// remove cookie
		if ($this->mode === 'cookie') {
			$this->sessions->cookie()->remove();
		}
	}

	/**
	 * Gets or sets the session duration
	 * Setting the duration also updates the expiry time and regenerates the session token
	 *
	 * @param int|null $duration Optional new duration in seconds to set
	 * @return int Number of seconds
	 */
	public function duration(int|null $duration = null): int
	{
		if ($duration !== null) {
			// verify that the duration is at least 1 second
			if ($duration < 1) {
				throw new InvalidArgumentException(
					data: [
						'method'   => 'Session::duration',
						'argument' => '$duration'
					],
					translate: false
				);
			}

			$this->prepareForWriting();
			$this->duration   = $duration;
			$this->expiryTime = time() + $duration;
			$this->regenerateTokenIfNotNew();
		}

		return $this->duration;
	}

	/**
	 * Initially generates the token for new sessions
	 * Used internally
	 */
	public function ensureToken(): void
	{
		if ($this->token === null) {
			$this->regenerateToken();
		}
	}

	/**
	 * Gets or sets the session expiry time
	 * Setting the expiry time also updates the duration and regenerates the session token
	 *
	 * @param string|int|null $expiryTime Optional new expiry timestamp or time string to set
	 * @return int Timestamp
	 */
	public function expiryTime(string|int|null $expiryTime = null): int
	{
		if ($expiryTime !== null) {
			// convert to a timestamp
			$expiryTime = static::timeToTimestamp($expiryTime);
			$now        = time();

			// verify that the expiry time is not in the past
			if ($expiryTime <= $now) {
				throw new InvalidArgumentException(
					data: [
						'method'   => 'Session::expiryTime',
						'argument' => '$expiryTime'
					],
					translate: false
				);
			}

			$this->prepareForWriting();
			$this->expiryTime = $expiryTime;
			$this->duration   = $expiryTime - $now;
			$this->regenerateTokenIfNotNew();
		}

		return $this->expiryTime;
	}

	/**
	 * @see \Kirby\Session\Data::get()
	 * @since 6.0.0
	 */
	public function get(string|null $key = null, mixed $default = null): mixed
	{
		return $this->data()->get($key, $default);
	}

	/**
	 * @see \Kirby\Session\Data::increment()
	 * @since 6.0.0
	 */
	public function increment(
		string|array $key,
		int $by = 1,
		int|null $max = null
	): void {
		$this->data()->increment($key, $by, $max);
	}

	/**
	 * Loads the session data from the session store
	 */
	protected function init(): void
	{
		// sessions that are new, written to or that have been destroyed should never be initialized
		if (
			$this->token === null ||
			$this->writeMode === true ||
			$this->destroyed === true
		) {
			// unexpected error that shouldn't occur
			throw new Exception(translate: false); // @codeCoverageIgnore
		}

		$token = $this->token;

		// make sure that the session exists
		if ($this->sessions->store()->exists($token->expiry, $token->id) !== true) {
			throw new NotFoundException(
				key: 'session.notFound',
				data: ['token' => $this->token()],
				fallback: 'Session "' . $this->token() . '" does not exist',
				translate: false,
				httpCode: 404
			);
		}

		// get the session data from the store
		$data = $this->sessions->store()->get(
			$token->expiry,
			$token->id
		);

		// verify HMAC
		// skip if we don't have the key (only the case for moved sessions)
		$hmac = Str::before($data, "\n");
		$data = trim(Str::after($data, "\n"));

		if (
			$token->key !== null &&
			hash_equals(hash_hmac('sha256', $data, $token->key), $hmac) !== true
		) {
			$this->throwInvalid();
		}

		// decode the serialized data
		$data = @unserialize($data, ['allowed_classes' => false]);

		if ($data === false) {
			$this->throwInvalid();
		}

		// verify start and expiry time
		$now = time();

		if (
			$now < $data['startTime'] ||
			$now > $data['expiryTime']
		) {
			$this->throwInvalid();
		}

		// follow to the new session if there is one
		if (isset($data['newSession']) === true) {
			// decrypt the token key if provided and we have access to
			// the PHP `sodium` extension for decryption
			if (
				isset($data['newSessionKey']) === true &&
				$crypto = $token->crypto()
			) {
				$tokenKey = $crypto->decrypt($data['newSessionKey']);

				$this->token = Token::parse($data['newSession'] . '.' . $tokenKey);
				$this->init();
				return;
			}

			// otherwise initialize without the token key (read-only mode)
			$this->token = Token::parse($data['newSession'], key: false);
			$this->init();
			return;
		}

		// verify timeout
		if (is_int($data['timeout']) === true) {
			if ($now - $data['lastActivity'] > $data['timeout']) {
				$this->throwInvalid();
			}

			// set a new activity timestamp, but only every few minutes for
			// better performance; don't do this if another call to init()
			// is already doing it to prevent endless loops;
			// also don't do this for read-only sessions
			if (
				$this->updatingLastActivity === false &&
				$token->key !== null &&
				$now - $data['lastActivity'] > $data['timeout'] / 15
			) {
				$this->updatingLastActivity = true;
				$this->prepareForWriting();

				// the remaining init steps have been done
				// by prepareForWriting()
				$this->lastActivity         = $now;
				$this->updatingLastActivity = false;
				return;
			}
		}

		// (re)initialize all instance variables
		$this->startTime    = $data['startTime'];
		$this->expiryTime   = $data['expiryTime'];
		$this->duration     = $data['duration'];
		$this->timeout      = $data['timeout'];
		$this->lastActivity = $data['lastActivity'];
		$this->renewable    = $data['renewable'];

		// reload data into existing object to avoid breaking memory references
		if (isset($this->data) === true) {
			$this->data()->reload($data['data']);
		} else {
			$this->data = new Data($this, $data['data']);
		}
	}

	/**
	 * Gets or sets the transmission mode
	 * Setting only works for new sessions that haven't been transmitted yet
	 *
	 * @param string|null $mode Optional new transmission mode
	 * @return string Transmission mode
	 */
	public function mode(string|null $mode = null): string
	{
		if ($mode === null) {
			return $this->mode;
		}

		// only allow this if this is a new session, otherwise the change
		// might not be applied correctly to the current request
		if ($this->token() !== null) {
			throw new InvalidArgumentException(
				data: ['method' => 'Session::mode', 'argument' => '$mode'],
				translate: false
			);
		}

		return $this->mode = $mode;
	}

	/**
	 * Checks if the session can be renewed and if the last renewal
	 * was more than half a session duration ago
	 */
	protected function needsRenewal(): bool
	{
		return
			$this->renewable() === true &&
			$this->expiryTime() - time() < $this->duration() / 2;
	}

	/**
	 * Returns whether the session token needs to be retransmitted to the client
	 * Only relevant in header and manual modes
	 */
	public function needsRetransmission(): bool
	{
		return $this->needsRetransmission;
	}

	/**
	 * Puts the session into write mode by acquiring a lock
	 * and reloading the data
	 * @unstable
	 */
	public function prepareForWriting(): void
	{
		// verify that we need to get into write mode:
		// - new sessions are only written to if the token has explicitly been ensured
		//   using $session->ensureToken() -> lazy session creation
		// - destroyed sessions are never written to
		// - no need to lock and re-init if we are already in write mode
		if (
			$this->token === null ||
			$this->destroyed === true ||
			$this->writeMode === true
		) {
			return;
		}

		$token = $this->token;

		// don't allow writing for read-only sessions
		// (only the case for moved sessions when the PHP `sodium` extension is not available)
		if ($token->key === null) {
			throw new LogicException(
				key: 'session.readonly',
				data: ['token' => $this->token()],
				fallback: 'Session "' . $this->token() . '" is currently read-only because it was accessed via an old session token',
				translate: false
			);
		}

		$this->sessions->store()->lock($token->expiry, $token->id);
		$this->init();
		$this->writeMode = true;
	}

	/**
	 * @see \Kirby\Session\Data::pull()
	 * @since 6.0.0
	 */
	public function pull(string $key, mixed $default = null): mixed
	{
		return $this->data()->pull($key, $default);
	}

	/**
	 * Regenerates the session token
	 * The old token will keep its validity for a 30 second grace period
	 */
	public function regenerateToken(): void
	{
		// don't do anything for destroyed sessions
		if ($this->destroyed === true) {
			return;
		}

		$this->prepareForWriting();

		// generate a new token
		$token = Token::generate($this->sessions->store(), $this->expiryTime);

		// mark the old session as moved if there is one
		if ($this->token !== null) {
			$this->newSession = [$token->toString(key: false), $token->key];
			$this->commit();

			// we are now in the context of the new session
			$this->newSession = null;
		}

		// set the new token
		$this->token = $token;

		// the new session needs to be written for the first time
		$this->writeMode = true;

		// (re)transmit session token
		if ($this->mode === 'cookie') {
			$this->sessions->cookie()->set($this->token(), $token->expiry);
		} else {
			$this->needsRetransmission = true;
		}

		// update the session in the Sessions instance's cache
		$this->sessions->update($this);
	}

	/**
	 * Regenerate session token, but only if there is already one
	 */
	protected function regenerateTokenIfNotNew(): void
	{
		if ($this->token !== null) {
			$this->regenerateToken();
		}
	}

	/**
	 * @see \Kirby\Session\Data::remove()
	 * @since 6.0.0
	 */
	public function remove(string|array $key): void
	{
		$this->data()->remove($key);
	}

	/**
	 * Renews the session with the same session duration
	 * Renewing also regenerates the session token
	 */
	public function renew(): void
	{
		if ($this->renewable() !== true) {
			throw new LogicException(
				key: 'session.notRenewable',
				fallback: 'Cannot renew a session that is not renewable, call $session->renewable(true) first',
				translate: false,
			);
		}

		$this->prepareForWriting();
		$this->expiryTime = time() + $this->duration();
		$this->regenerateTokenIfNotNew();
	}

	/**
	 * Gets or sets the renewable flag
	 * Automatically renews the session if renewing gets enabled
	 *
	 * @param bool|null $renewable Optional new renewable flag to set
	 */
	public function renewable(bool|null $renewable = null): bool
	{
		if ($renewable !== null) {
			$this->prepareForWriting();
			$this->renewable = $renewable;
			$this->autoRenew();
		}

		return $this->renewable;
	}

	/**
	 * @see \Kirby\Session\Data::set()
	 * @since 6.0.0
	 */
	public function set(string|array $key, mixed $value = null): void
	{
		$this->data()->set($key, $value);
	}

	/**
	 * Gets the session start time
	 *
	 * @return int Timestamp
	 */
	public function startTime(): int
	{
		return $this->startTime;
	}

	/**
	 * Throws an exception for an invalid session
	 * @since 6.0.0
	 *
	 * @throws \Kirby\Exception\LogicException
	 */
	protected function throwInvalid(): never
	{
		throw new LogicException(
			key: 'session.invalid',
			data: ['token' => $this->token()],
			fallback: 'Session "' . $this->token() . '" is invalid',
			translate: false,
			httpCode: 500
		);
	}

	/**
	 * Gets or sets the session timeout
	 *
	 * @param int|false|null $timeout Optional new timeout to set or false to disable timeout
	 * @return int|false Number of seconds or false for "no timeout"
	 */
	public function timeout(int|false|null $timeout = null): int|false
	{
		if ($timeout !== null) {
			// verify that the timeout is at least 1 second
			if (is_int($timeout) === true && $timeout < 1) {
				throw new InvalidArgumentException(
					data: [
						'method'   => 'Session::timeout',
						'argument' => '$timeout'
					],
					translate: false
				);
			}

			$this->prepareForWriting();
			$this->timeout      = $timeout;
			$this->lastActivity = is_int($timeout) ? time() : null;
		}

		return $this->timeout;
	}

	/**
	 * Makes sure that the given value is a valid timestamp
	 *
	 * @param string|int $time Timestamp or date string (must be supported by `strtotime()`)
	 * @param int|null $now Timestamp to use as a base for the calculation of relative dates
	 * @return int Timestamp value
	 */
	protected static function timeToTimestamp(
		string|int $time,
		int|null $now = null
	): int {
		// default to current time as $now
		$now ??= time();

		// convert date strings to a timestamp first
		if (is_string($time) === true) {
			$timestamp = strtotime($time, $now);

			if ($timestamp === false) {
				throw new InvalidArgumentException(
					message: 'Invalid time string: ' . $time
				);
			}

			return $timestamp;
		}

		return $time;
	}

	/**
	 * Gets the session token or null
	 * if the session doesn't have a token yet
	 */
	public function token(): string|null
	{
		return $this->token?->toString();
	}
}
