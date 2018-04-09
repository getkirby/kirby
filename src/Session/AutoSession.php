<?php

namespace Kirby\Session;

/**
 * AutoSession - simplified session handler with fully automatic session creation
 *
 * @package   Kirby Session
 * @author    Lukas Bestle <lukas@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class AutoSession
{
    protected $sessions;
    protected $options;

    protected $createdSession;

    /**
     * Creates a new AutoSession instance
     *
     * @param SessionStore|string $store   SessionStore object or a path to the storage directory (uses the FileSessionStore)
     * @param array               $options Optional additional options:
     *                                     - `durationNormal`: Duration of normal sessions in seconds
     *                                                         Defaults to 2 hours
     *                                     - `durationLong`:   Duration of "remember me" sessions in seconds
     *                                                         Defaults to 2 weeks
     *                                     - `timeout`:        Activity timeout in seconds (integer or false for none)
     *                                                         *Only* used for normal sessions
     *                                                         Defaults to `1800` (half an hour)
     *                                     - `cookieName`:     Name to use for the session cookie
     *                                                         Defaults to `kirby_session`
     *                                     - `gcInterval`:     How often should the garbage collector be run?
     *                                                         Integer or `false` for never; defaults to `100`
     *
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
     * @param  array   $options Optional additional options:
     *                          - `detect`:     Whether to allow sessions in the `Authorization` HTTP header (`true`)
     *                                          or only in the session cookie (`false`)
     *                                          Defaults to `false`
     *                          - `createMode`: When creating a new session, should it be set as a cookie or is it going
     *                                          to be transmitted manually to be used in a header?
     *                                          Defaults to `cookie`
     *                          - `long`:       Whether the session is a long "remember me" session or a normal session
     *                                          Defaults to `false`
     * @return Session
     */
    public function get(array $options = []): Session
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
            $session->duration($duration);
        }
        if (($timeout === false && $session->timeout() !== false) || $timeout > $session->timeout()) {
            $session->timeout($timeout);
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
     * @param  array   $options Optional additional options:
     *                          - `startTime`:  Time the session starts being valid (date string or timestamp)
     *                                          Defaults to `now`
     *                          - `expiryTime`: Time the session expires (date string or timestamp)
     *                                          Defaults to `+ 2 hours`
     *                          - `timeout`:    Activity timeout in seconds (integer or false for none)
     *                                          Defaults to `1800` (half an hour)
     *                          - `renewable`:  Should it be possible to extend the expiry date?
     *                                          Defaults to `true`
     * @return Session
     */
    public function createManually(array $options = []): Session
    {
        // only ever allow manual transmission mode
        // to prevent overwriting our "auto" session
        $options['mode'] = 'manual';

        return $this->sessions->create($options);
    }

    /**
     * Deletes all expired sessions
     *
     * If the `gcInterval` is configured, this is done automatically
     * when intializing the AutoSession class
     *
     * @return void
     */
    public function collectGarbage()
    {
        $this->sessions->collectGarbage();
    }
}
