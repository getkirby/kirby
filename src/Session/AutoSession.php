<?php

namespace Kirby\Session;

/**
 * AutoSession - simplified session handler with fully automatic session creation
 *
 * @package   Kirby Session
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class AutoSession
{
    protected $sessions;
    protected $options;

    protected $createdSession;

    /**
     * Creates a new AutoSession instance
     *
     * @param \Kirby\Session\SessionStore|string $store SessionStore object or a path to the storage directory (uses the FileSessionStore)
     * @param array $options Optional additional options:
     *                       - `durationNormal`: Duration of normal sessions in seconds; defaults to 2 hours
     *                       - `durationLong`: Duration of "remember me" sessions in seconds; defaults to 2 weeks
     *                       - `timeout`: Activity timeout in seconds (integer or false for none); *only* used for normal sessions; defaults to `1800` (half an hour)
     *                       - `cookieName`: Name to use for the session cookie; defaults to `kirby_session`
     *                       - `gcInterval`: How often should the garbage collector be run?; integer or `false` for never; defaults to `100`
     */
    public function __construct($store, array $options = [])
    {
        // merge options with defaults
        $this->options = array_merge([
            'durationNormal' => 7200,
            'durationLong'   => 1209600,
            'timeout'        => 1800,
            'cookieName'     => 'kirby_session',
            'gcInterval'     => 100
        ], $options);

        // create an internal instance of the low-level Sessions class
        $this->sessions = new Sessions($store, [
            'cookieName' => $this->options['cookieName'],
            'gcInterval' => $this->options['gcInterval']
        ]);
    }

    /**
     * Returns the automatic session
     *
     * @param array $options Optional additional options:
     *                       - `detect`: Whether to allow sessions in the `Authorization` HTTP header (`true`) or only in the session cookie (`false`); defaults to `false`
     *                       - `createMode`: When creating a new session, should it be set as a cookie or is it going to be transmitted manually to be used in a header?; defaults to `cookie`
     *                       - `long`: Whether the session is a long "remember me" session or a normal session; defaults to `false`
     * @return \Kirby\Session\Session
     */
    public function get(array $options = [])
    {
        // merge options with defaults
        $options = array_merge([
            'detect'     => false,
            'createMode' => 'cookie',
            'long'       => false
        ], $options);

        // determine expiry options based on the session type
        if ($options['long'] === true) {
            $duration = $this->options['durationLong'];
            $timeout  = false;
        } else {
            $duration = $this->options['durationNormal'];
            $timeout  = $this->options['timeout'];
        }

        // get the current session
        if ($options['detect'] === true) {
            $session = $this->sessions->currentDetected();
        } else {
            $session = $this->sessions->current();
        }

        // create a new session
        if ($session === null) {
            $session = $this->createdSession ?? $this->sessions->create([
                'mode'       => $options['createMode'],
                'startTime'  => time(),
                'expiryTime' => time() + $duration,
                'timeout'    => $timeout,
                'renewable'  => true,
            ]);

            // cache the newly created session to ensure that we don't create multiple
            $this->createdSession = $session;
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
            } elseif (is_int($timeout) && $timeout > $session->timeout()) {
                // it needs to be extended
                $session->timeout($timeout);
            }
        }

        // if the session has been created and was not yet initialized,
        // update the mode to a custom mode
        // don't update back to cookie mode because the "special" behavior always wins
        if ($session->token() === null && $options['createMode'] !== 'cookie') {
            $session->mode($options['createMode']);
        }

        return $session;
    }

    /**
     * Creates a new empty session that is *not* automatically transmitted to the client
     * Useful for custom applications like a password reset link
     * Does *not* affect the automatic session
     *
     * @param array $options Optional additional options:
     *                       - `startTime`: Time the session starts being valid (date string or timestamp); defaults to `now`
     *                       - `expiryTime`: Time the session expires (date string or timestamp); defaults to `+ 2 hours`
     *                       - `timeout`: Activity timeout in seconds (integer or false for none); defaults to `1800` (half an hour)
     *                       - `renewable`: Should it be possible to extend the expiry date?; defaults to `true`
     * @return \Kirby\Session\Session
     */
    public function createManually(array $options = [])
    {
        // only ever allow manual transmission mode
        // to prevent overwriting our "auto" session
        $options['mode'] = 'manual';

        return $this->sessions->create($options);
    }

    /**
     * Returns the specified Session object
     * @since 3.3.1
     *
     * @param string $token Session token, either including or without the key
     * @return \Kirby\Session\Session
     */
    public function getManually(string $token)
    {
        return $this->sessions->get($token, 'manual');
    }

    /**
     * Deletes all expired sessions
     *
     * If the `gcInterval` is configured, this is done automatically
     * when initializing the AutoSession class
     *
     * @return void
     */
    public function collectGarbage()
    {
        $this->sessions->collectGarbage();
    }
}
