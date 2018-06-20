<?php

namespace Kirby\Session;

use Throwable;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Kirby\Data\Json;
use Kirby\Exception\BadMethodCallException;
use Kirby\Exception\InvalidArgumentException;
use Kirby\Exception\LogicException;
use Kirby\Exception\NotFoundException;
use Kirby\Http\Cookie;
use Kirby\Http\Url;

/**
 * @package   Kirby Session
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class Session
{
    // parent data
    protected $sessions;
    protected $mode;

    // parts of the token
    protected $tokenExpiry;
    protected $tokenId;
    protected $tokenKey;

    // persistent data
    protected $startTime;
    protected $expiryTime;
    protected $duration;
    protected $timeout;
    protected $lastActivity;
    protected $renewable;
    protected $data;
    protected $newSession;

    // temporary data
    protected $keyObject;
    protected $updatingLastActivity = false;
    protected $destroyed = false;
    protected $writeMode = false;
    protected $needsRetransmission = false;

    /**
     * Creates a new Session instance
     *
     * @param Sessions    $sessions Parent sessions object
     * @param string|null $token    Session token or null for a new session
     * @param array       $options  Optional additional options:
     *                              - `mode`:       Token transmission mode (cookie or manual)
     *                                              Defaults to `cookie`
     *                              - `startTime`:  Time the session starts being valid (date string or timestamp)
     *                                              Defaults to `now`
     *                              - `expiryTime`: Time the session expires (date string or timestamp)
     *                                              Defaults to `+ 2 hours`
     *                              - `timeout`:    Activity timeout in seconds (integer or false for none)
     *                                              Defaults to `1800` (half an hour)
     *                              - `renewable`:  Should it be possible to extend the expiry date?
     *                                              Defaults to `true`
     */
    public function __construct(Sessions $sessions, $token, array $options)
    {
        $this->sessions = $sessions;
        $this->mode     = $options['mode'] ?? 'cookie';

        if (is_string($token)) {
            // existing session

            $this->parseToken($token);
            $this->init();
            $this->autoRenew();
        } elseif ($token === null) {
            // new session

            // set data based on options
            $this->startTime  = static::timeToTimestamp($options['startTime'] ?? time());
            $this->expiryTime = static::timeToTimestamp($options['expiryTime'] ?? '+ 2 hours', $this->startTime);
            $this->duration   = $this->expiryTime - $this->startTime;
            $this->timeout    = $options['timeout'] ?? 1800;
            $this->renewable  = $options['renewable'] ?? true;
            $this->data       = new SessionData($this, []);

            // validate persistent data
            if (time() > $this->expiryTime) {
                // session must not already be expired, but the start time may be in the future
                throw new InvalidArgumentException([
                    'data'      => ['method' => 'Session::__construct', 'argument' => '$options[\'expiryTime\']'],
                    'translate' => false
                ]);
            }
            if ($this->duration < 0) {
                // expiry time must be after start time
                throw new InvalidArgumentException([
                    'data'      => ['method' => 'Session::__construct', 'argument' => '$options[\'startTime\' & \'expiryTime\']'],
                    'translate' => false
                ]);
            }
            if (!is_int($this->timeout) && $this->timeout !== false) {
                throw new InvalidArgumentException([
                    'data'      => ['method' => 'Session::__construct', 'argument' => '$options[\'timeout\']'],
                    'translate' => false
                ]);
            }
            if (!is_bool($this->renewable)) {
                throw new InvalidArgumentException([
                    'data'      => ['method' => 'Session::__construct', 'argument' => '$options[\'renewable\']'],
                    'translate' => false
                ]);
            }

            // set activity time if a timeout was requested
            if (is_int($this->timeout)) {
                $this->lastActivity = time();
            }
        } else {
            throw new InvalidArgumentException([
                'data'      => ['method' => 'Session::__construct', 'argument' => '$token'],
                'translate' => false
            ]);
        }

        // ensure that all changes are committed on script termination
        register_shutdown_function([$this, 'commit']);
    }

    /**
     * Gets the session token or null if the session doesn't have a token yet
     *
     * @return string|null
     */
    public function token()
    {
        if ($this->tokenExpiry !== null) {
            return $this->tokenExpiry . '.' . $this->tokenId . '.' . $this->tokenKey;
        } else {
            return null;
        }
    }

    /**
     * Gets or sets the transmission mode
     * Setting only works for new sessions that haven't been transmitted yet
     *
     * @param  string $mode Optional new transmission mode
     * @return string       Transmission mode
     */
    public function mode(string $mode = null)
    {
        if (is_string($mode)) {
            // only allow this if this is a new session, otherwise the change
            // might not be applied correctly to the current request
            if ($this->token() !== null) {
                throw new InvalidArgumentException([
                    'data'      => ['method' => 'Session::mode', 'argument' => '$mode'],
                    'translate' => false
                ]);
            }

            $this->mode = $mode;
        }

        return $this->mode;
    }

    /**
     * Gets the session start time
     *
     * @return integer Timestamp
     */
    public function startTime(): int
    {
        return $this->startTime;
    }

    /**
     * Gets or sets the session expiry time
     * Setting the expiry time also updates the duration and regenerates the session token
     *
     * @param  string|integer $expiryTime Optional new expiry timestamp or time string to set
     * @return integer                    Timestamp
     */
    public function expiryTime($expiryTime = null): int
    {
        if (is_string($expiryTime) || is_int($expiryTime)) {
            // convert to a timestamp
            $expiryTime = static::timeToTimestamp($expiryTime);

            // verify that the expiry time is not in the past
            if ($expiryTime <= time()) {
                throw new InvalidArgumentException([
                    'data'      => ['method' => 'Session::expiryTime', 'argument' => '$expiryTime'],
                    'translate' => false
                ]);
            }

            $this->prepareForWriting();
            $this->expiryTime = $expiryTime;
            $this->duration   = $expiryTime - time();
            $this->regenerateTokenIfNotNew();
        } elseif ($expiryTime !== null) {
            throw new InvalidArgumentException([
                'data'      => ['method' => 'Session::expiryTime', 'argument' => '$expiryTime'],
                'translate' => false
            ]);
        }

        return $this->expiryTime;
    }

    /**
     * Gets or sets the session duration
     * Setting the duration also updates the expiry time and regenerates the session token
     *
     * @param  integer $duration Optional new duration in seconds to set
     * @return integer           Number of seconds
     */
    public function duration(int $duration = null): int
    {
        if (is_int($duration)) {
            // verify that the duration is at least 1 second
            if ($duration <= 0) {
                throw new InvalidArgumentException([
                    'data'      => ['method' => 'Session::duration', 'argument' => '$duration'],
                    'translate' => false
                ]);
            }

            $this->prepareForWriting();
            $this->duration   = $duration;
            $this->expiryTime = time() + $duration;
            $this->regenerateTokenIfNotNew();
        }

        return $this->duration;
    }

    /**
     * Gets or sets the session timeout
     *
     * @param  integer|false $timeout Optional new timeout to set or false to disable timeout
     * @return integer|false          Number of seconds or false for "no timeout"
     */
    public function timeout($timeout = null)
    {
        if (is_int($timeout) || $timeout === false) {
            // verify that the timeout is at least 1 second
            if (is_int($timeout) && $timeout <= 0) {
                throw new InvalidArgumentException([
                    'data'      => ['method' => 'Session::timeout', 'argument' => '$timeout'],
                    'translate' => false
                ]);
            }

            $this->prepareForWriting();
            $this->timeout = $timeout;

            if (is_int($timeout)) {
                $this->lastActivity = time();
            } else {
                $this->lastActivity = null;
            }
        } elseif ($timeout !== null) {
            throw new InvalidArgumentException([
                'data'      => ['method' => 'Session::timeout', 'argument' => '$timeout'],
                'translate' => false
            ]);
        }

        return $this->timeout;
    }

    /**
     * Gets or sets the renewable flag
     * Automatically renews the session if renewing gets enabled
     *
     * @param  boolean $renewable Optional new renewable flag to set
     * @return boolean
     */
    public function renewable(bool $renewable = null): bool
    {
        if (is_bool($renewable)) {
            $this->prepareForWriting();
            $this->renewable = $renewable;
            $this->autoRenew();
        }

        return $this->renewable;
    }

    /**
     * Returns the session data object
     *
     * @return SessionData
     */
    public function data(): SessionData
    {
        return $this->data;
    }

    /**
     * Magic call method that proxies all calls to session data methods
     *
     * @param  string $name      Method name (one of set, increment, decrement, get, pull, remove, clear)
     * @param  array  $arguments Method arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        // validate that we can handle the called method
        if (!in_array($name, ['set', 'increment', 'decrement', 'get', 'pull', 'remove', 'clear'])) {
            throw new BadMethodCallException([
                'data'      => ['method' => 'Session::' . $name],
                'translate' => false
            ]);
        }

        return $this->data()->$name(...$arguments);
    }

    /**
     * Writes all changes to the session to the session store
     *
     * @return void
     */
    public function commit()
    {
        // nothing to do if nothing changed or the session has been just created or destroyed
        if ($this->writeMode !== true || $this->tokenExpiry === null || $this->destroyed === true) {
            return;
        }

        // collect all data
        if ($this->newSession) {
            // the token has changed
            // we are writing to the old session: it only gets the reference to the new session
            // and a shortened expiry time (30 second grace period)
            $data = [
                'startTime'  => $this->startTime(),
                'expiryTime' => time() + 30,
                'newSession' => $this->newSession
            ];
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

        // encrypt the data with the token key
        $data = Json::encode($data);
        $data = Crypto::encrypt($data, $this->keyObject, true);

        // store the data
        $this->sessions->store()->set($this->tokenExpiry, $this->tokenId, $data);
        $this->sessions->store()->unlock($this->tokenExpiry, $this->tokenId);
        $this->writeMode = false;
    }

    /**
     * Entirely destroys the session
     *
     * @return void
     */
    public function destroy()
    {
        // no need to destroy new or destroyed sessions
        if ($this->tokenExpiry === null || $this->destroyed === true) {
            return;
        }

        // remove session file
        $this->sessions->store()->destroy($this->tokenExpiry, $this->tokenId);
        $this->destroyed           = true;
        $this->writeMode           = false;
        $this->needsRetransmission = false;

        // remove cookie
        if ($this->mode === 'cookie') {
            Cookie::remove($this->sessions->cookieName());
        }
    }

    /**
     * Renews the session with the same session duration
     * Renewing also regenerates the session token
     *
     * @return void
     */
    public function renew()
    {
        if ($this->renewable() !== true) {
            throw new LogicException([
                'key'       => 'session.notRenewable',
                'fallback'  => 'Cannot renew a session that is not renewable, call $session->renewable(true) first',
                'translate' => false,
            ]);
        }

        $this->prepareForWriting();
        $this->expiryTime = time() + $this->duration();
        $this->regenerateTokenIfNotNew();
    }

    /**
     * Regenerates the session token
     * The old token will keep its validity for a 30 second grace period
     *
     * @return void
     */
    public function regenerateToken()
    {
        // don't do anything for destroyed sessions
        if ($this->destroyed === true) {
            return;
        }

        $this->prepareForWriting();

        // generate new $tokenId
        $tokenExpiry = $this->expiryTime;
        $tokenId     = $this->sessions->store()->createId($tokenExpiry);

        // generate new key
        $keyObject = Key::createNewRandomKey();
        $tokenKey  = $keyObject->saveToAsciiSafeString();

        // mark the old session as moved if there is one
        if ($this->tokenExpiry !== null) {
            $this->newSession = $tokenExpiry . '.' . $tokenId . '.' . $tokenKey;
            $this->commit();

            // we are now in the context of the new session
            $this->newSession = null;
        }

        // set new data as instance vars
        $this->tokenExpiry = $tokenExpiry;
        $this->tokenId     = $tokenId;
        $this->tokenKey    = $tokenKey;
        $this->keyObject   = $keyObject;

        // the new session needs to be written for the first time
        $this->writeMode = true;

        // (re)transmit session token
        if ($this->mode === 'cookie') {
            Cookie::set($this->sessions->cookieName(), $this->token(), [
                'lifetime' => $this->tokenExpiry,
                'path'     => Url::index(['host' => null, 'trailingSlash' => true]),
                'secure'   => Url::scheme() === 'https',
                'httpOnly' => true
            ]);
        } else {
            $this->needsRetransmission = true;
        }
    }

    /**
     * Returns whether the session token needs to be retransmitted to the client
     * Only relevant in header and manual modes
     *
     * @return boolean
     */
    public function needsRetransmission(): bool
    {
        return $this->needsRetransmission;
    }

    /**
     * Ensures that all pending changes are written to disk before the object is destructed
     */
    public function __destruct()
    {
        $this->commit();
    }

    /**
     * Initially generates the token for new sessions
     * Used internally
     *
     * @return void
     */
    public function ensureToken()
    {
        if ($this->tokenExpiry === null) {
            $this->regenerateToken();
        }
    }

    /**
     * Puts the session into write mode by acquiring a lock and reloading the data
     * Used internally
     *
     * @return void
     */
    public function prepareForWriting()
    {
        // verify that we need to get into write mode:
        // - new sessions are only written to if the token has explicitly been ensured
        //   using $session->ensureToken() -> lazy session creation
        // - destroyed sessions are never written to
        // - no need to lock and re-init if we are already in write mode
        if ($this->tokenExpiry === null || $this->destroyed === true || $this->writeMode === true) {
            return;
        }

        $this->sessions->store()->lock($this->tokenExpiry, $this->tokenId);
        $this->init();
        $this->writeMode = true;
    }

    /**
     * Parses a token string into its parts and sets them as instance vars
     *
     * @param  string $token Session token
     * @return void
     */
    protected function parseToken(string $token)
    {
        // split the token into its parts
        $parts = explode('.', $token);

        // only continue if the token has exactly three parts
        if (count($parts) !== 3) {
            throw new InvalidArgumentException([
                'data'      => ['method' => 'Session::parseToken', 'argument' => '$token'],
                'translate' => false
            ]);
        }

        $tokenExpiry = (int)$parts[0];
        $tokenId     = $parts[1];
        $tokenKey    = $parts[2];

        // verify that all parts were parsed correctly using reassembly
        if ($tokenExpiry . '.' . $tokenId . '.' . $tokenKey !== $token) {
            throw new InvalidArgumentException([
                'data'      => ['method' => 'Session::parseToken', 'argument' => '$token'],
                'translate' => false
            ]);
        }

        // create the key object from the hexadecimal key in the token
        try {
            $keyObject = Key::loadFromAsciiSafeString($tokenKey);
        } catch (Throwable $e) {
            throw new InvalidArgumentException([
                'data'      => ['method' => 'Session::parseToken', 'argument' => '$token'],
                'translate' => false
            ]);
        }

        $this->tokenExpiry = $tokenExpiry;
        $this->tokenId     = $tokenId;
        $this->tokenKey    = $tokenKey;
        $this->keyObject   = $keyObject;
    }

    /**
     * Makes sure that the given value is a valid timestamp
     *
     * @param  string|integer $time Timestamp or date string (must be supported by `strtotime()`)
     * @param  integer        $now  Timestamp to use as a base for the calculation of relative dates
     * @return integer              Timestamp value
     */
    protected static function timeToTimestamp($time, int $now = null): int
    {
        // default to current time as $now
        if (!is_int($now)) {
            $now = time();
        }

        // convert date strings to a timestamp first
        if (is_string($time)) {
            $time = strtotime($time, $now);
        }

        // now make sure that we have a valid timestamp
        if (is_int($time)) {
            return $time;
        } else {
            throw new InvalidArgumentException([
                'data'      => ['method' => 'Session::timeToTimestamp', 'argument' => '$time'],
                'translate' => false
            ]);
        }
    }

    /**
     * Loads the session data from the session store
     *
     * @return void
     */
    protected function init()
    {
        // sessions that are new, written to or that have been destroyed should never be initialized
        if ($this->tokenExpiry === null || $this->writeMode === true || $this->destroyed === true) {
            // unexpected error that shouldn't occur
            throw new Exception(['translate' => false]); // @codeCoverageIgnore
        }

        // make sure that the session exists
        if ($this->sessions->store()->exists($this->tokenExpiry, $this->tokenId) !== true) {
            throw new NotFoundException([
                'key'       => 'session.notFound',
                'data'      => ['token' => $this->token()],
                'fallback'  => 'Session "' . $this->token() . '" does not exist',
                'translate' => false,
                'httpCode'  => 404
            ]);
        }

        // get the session data from the store
        $data = $this->sessions->store()->get($this->tokenExpiry, $this->tokenId);

        // decrypt the data
        try {
            $data = Crypto::decrypt($data, $this->keyObject, true);
            $data = Json::decode($data);
        } catch (Throwable $e) {
            throw new LogicException([
                'key'       => 'session.invalid',
                'data'      => ['token' => $this->token()],
                'fallback'  => 'Session "' . $this->token() . '" is invalid',
                'translate' => false,
                'httpCode'  => 500
            ]);
        }

        // verify start and expiry time
        if (time() < $data['startTime'] || time() > $data['expiryTime']) {
            throw new LogicException([
                'key'       => 'session.invalid',
                'data'      => ['token' => $this->token()],
                'fallback'  => 'Session "' . $this->token() . '" is invalid',
                'translate' => false,
                'httpCode'  => 500
            ]);
        }

        // follow to the new session if there is one
        if (isset($data['newSession'])) {
            $this->parseToken($data['newSession']);
            return $this->init();
        }

        // verify timeout
        if (is_int($data['timeout'])) {
            if (time() - $data['lastActivity'] > $data['timeout']) {
                throw new LogicException([
                    'key'       => 'session.invalid',
                    'data'      => ['token' => $this->token()],
                    'fallback'  => 'Session "' . $this->token() . '" is invalid',
                    'translate' => false,
                    'httpCode'  => 500
                ]);
            }

            // set a new activity timestamp, but only every few minutes for better performance
            // don't do this if another call to init() is already doing it to prevent endless loops
            if ($this->updatingLastActivity === false && time() - $data['lastActivity'] > $data['timeout'] / 15) {
                $this->updatingLastActivity = true;
                $this->prepareForWriting();

                // the remaining init steps have been done by prepareForWriting()
                $this->lastActivity = time();
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
        if ($this->data) {
            $this->data()->reload($data['data']);
        } else {
            $this->data = new SessionData($this, $data['data']);
        }
    }

    /**
     * Regenerate session token, but only if there is already one
     *
     * @return void
     */
    protected function regenerateTokenIfNotNew()
    {
        if ($this->tokenExpiry !== null) {
            $this->regenerateToken();
        }
    }

    /**
     * Automatically renews the session if possible and necessary
     *
     * @return void
     */
    protected function autoRenew()
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
     * Checks if the session can be renewed and if the last renewal
     * was more than half a session duration ago
     *
     * @return boolean
     */
    protected function needsRenewal(): bool
    {
        return $this->renewable() === true && $this->expiryTime() - time() < $this->duration() / 2;
    }
}
