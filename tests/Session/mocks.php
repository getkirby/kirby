<?php

namespace Kirby\Session;

use Exception;
use Kirby\Toolkit\Str;

class InvalidSessionStore
{
}

class TestSessionStore extends SessionStore
{
    public $validKey   = '74686973206973207468652076616c6964206b657920696e2068657821203a29';
    public $invalidKey = '616e64207965702c2074686174277320616e2065617374657220656767e280a6';

    public $sessions = [];
    public $hmacs    = [];
    public $isLocked = [];
    public $collectedGarbage = false;

    public function __construct()
    {
        $time = time();

        // set test sessions
        $this->sessions = [
            // expired session
            '1000000000.expired' => [
                'startTime'    => 0,
                'expiryTime'   => 1000000000,
                'duration'     => 1000000000,
                'timeout'      => false,
                'lastActivity' => null,
                'renewable'    => false,
                'data' => [
                    'id' => 'expired'
                ]
            ],

            // valid session that needs to be renewed
            '2000000000.renewal' => [
                'startTime'    => 0,
                'expiryTime'   => 2000000000,
                'duration'     => 2000000000,
                'timeout'      => false,
                'lastActivity' => null,
                'renewable'    => true,
                'data' => [
                    'id' => 'renewal'
                ]
            ],

            // valid session that needs to be renewed, but can't
            '2000000000.nonRenewable' => [
                'startTime'    => 0,
                'expiryTime'   => 2000000000,
                'duration'     => 2000000000,
                'timeout'      => false,
                'lastActivity' => null,
                'renewable'    => false,
                'data' => [
                    'id' => 'nonRenewable'
                ]
            ],

            // invalid data structure
            '9999999999.invalidSerialization' => 'invalid-serialization',

            // invalid storage structure
            '9999999999.invalidStructure' => 'invalid-structure',

            // valid session that has moved
            '9999999999.moved' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'newSession'   => '9999999999.valid'
            ],

            // session that has moved but is expired
            '1000000000.movedExpired' => [
                'startTime'    => 0,
                'expiryTime'   => 1000000000,
                'newSession'   => '9999999999.valid'
            ],

            // session that has moved and the new session doesn't exist
            '9999999999.movedInvalid' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'newSession'   => '9999999999.doesNotExist'
            ],

            // valid session that has moved to a nearly expired session
            '9999999999.movedRenewal' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'newSession'   => '2000000000.renewal'
            ],

            // valid session that has moved to a session that could be refreshed
            '9999999999.movedTimeoutActivity' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'newSession'   => '9999999999.timeoutActivity2'
            ],

            // valid session that isn't yet started
            '9999999999.notStarted' => [
                'startTime'    => 7777777777,
                'expiryTime'   => 9999999999,
                'duration'     => 2222222222,
                'timeout'      => false,
                'lastActivity' => null,
                'renewable'    => false,
                'data' => [
                    'id' => 'notStarted'
                ]
            ],

            // session with an expired timeout
            '9999999999.timeout' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'duration'     => 9999999999,
                'timeout'      => 3600,
                'lastActivity' => 1000000000,
                'renewable'    => false,
                'data' => [
                    'id' => 'timeout'
                ]
            ],

            // session with a timeout that doesn't need to be refreshed
            '9999999999.timeoutActivity1' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'duration'     => 9999999999,
                'timeout'      => 3600,
                'lastActivity' => $time - 10,
                'renewable'    => false,
                'data' => [
                    'id'               => 'timeoutActivity1',
                    'expectedActivity' => $time - 10
                ]
            ],

            // session with a timeout that could be refreshed
            '9999999999.timeoutActivity2' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'duration'     => 9999999999,
                'timeout'      => 3600,
                'lastActivity' => $time - 500,
                'renewable'    => false,
                'data' => [
                    'id'               => 'timeoutActivity2',
                    'expectedActivity' => $time
                ]
            ],

            // valid session without any kind of validation issues
            '9999999999.valid' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'duration'     => 9999999999,
                'timeout'      => false,
                'lastActivity' => null,
                'renewable'    => false,
                'data' => [
                    'id' => 'valid'
                ]
            ],

            // another valid session
            '9999999999.valid2' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'duration'     => 9999999999,
                'timeout'      => false,
                'lastActivity' => null,
                'renewable'    => true,
                'data' => [
                    'id' => 'valid2'
                ]
            ]
        ];
    }

    public function createId(int $expiryTime): string
    {
        do {
            $id = $this->generateId();
        } while ($this->exists($expiryTime, $id));

        $this->sessions[$expiryTime . '.' . $id] = '';
        $this->lock($expiryTime, $id);

        return $id;
    }

    public function exists(int $expiryTime, string $id): bool
    {
        return isset($this->sessions[$expiryTime . '.' . $id]);
    }

    public function lock(int $expiryTime, string $id)
    {
        $this->isLocked[$expiryTime . '.' . $id] = true;
    }

    public function unlock(int $expiryTime, string $id)
    {
        unset($this->isLocked[$expiryTime . '.' . $id]);
    }

    public function get(int $expiryTime, string $id): string
    {
        $name = $expiryTime . '.' . $id;

        if ($this->exists($expiryTime, $id)) {
            $data = $this->sessions[$name];

            // special cases
            if ($data === 'invalid-serialization') {
                $data = 'some gibberish';
                return hash_hmac('sha256', $data, $this->validKey) . "\n" . $data;
            } elseif ($data === 'invalid-structure') {
                return 'some gibberish';
            }

            // check if this is a created or test session
            if (isset($this->hmacs[$name])) {
                // created session: it has its own HMAC, prepend it again

                return $this->hmacs[$name] . "\n" . serialize($data);
            } else {
                // test session, add an HMAC based on the $validKey

                $data = serialize($data);
                return hash_hmac('sha256', $data, $this->validKey) . "\n" . $data;
            }
        } else {
            throw new Exception('Session does not exist');
        }
    }

    public function set(int $expiryTime, string $id, string $data)
    {
        $name = $expiryTime . '.' . $id;

        if (!$this->exists($expiryTime, $id)) {
            throw new Exception('Session does not exist');
        }

        if (!isset($this->isLocked[$name])) {
            throw new Exception('Cannot write to a session that is not locked');
        }

        // decode the data
        $hmac = Str::before($data, "\n");
        $data = trim(Str::after($data, "\n"));
        $data = unserialize($data);

        // store the HMAC separately for the get() method above
        $this->hmacs[$name]    = $hmac;
        $this->sessions[$name] = $data;
    }

    public function destroy(int $expiryTime, string $id)
    {
        unset($this->sessions[$expiryTime . '.' . $id]);
    }

    public function collectGarbage()
    {
        $this->collectedGarbage = true;
    }
}

class MockSession extends Session
{
    public $ensuredToken       = false;
    public $preparedForWriting = false;

    public function __construct()
    {
        // do nothing here
    }

    public function ensureToken()
    {
        $this->ensuredToken = true;
    }

    public function prepareForWriting()
    {
        $this->preparedForWriting = true;
    }
}

/**
 * Mock for the PHP time() function to ensure reliable testing
 *
 * @return int A fake timestamp
 */
function time(): int
{
    if (defined('KIRBY_TESTING') !== true || KIRBY_TESTING !== true) {
        throw new Exception('Mock time() function was loaded outside of the test environment. This should never happen.');
    }

    return MockTime::$time;
}

class MockTime
{
    public static $time = 1337000000;
}
