<?php

namespace Kirby\Session;

use Exception;
use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;

class InvalidSessionStore
{

}

class TestSessionStore extends SessionStore
{
    public $validKey   = 'def000009c5f82228f47fda0a1577159d89c60bb9fdfd411781064f8d25900e3e90d6db39177f047d416945c8c74f7b610f1b41665737b8011dc6163a61bb37b993078ee';
    public $invalidKey = 'def00000763e647b3fc50df0ef1796a0b56d4bec341801c145c54ced6a7bdc862cda5c9b201caf27d1ed26703b453b9660700eb90c264e011ad49e5e8bb89a81718a9a59';

    public $keyObject;
    public $sessions;
    public $isLocked = [];
    public $collectedGarbage = false;

    public function __construct()
    {
        $time = time();

        // set key object
        $this->keyObject = Key::loadFromAsciiSafeString($this->validKey);

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
            '3000000000.renewal' => [
                'startTime'    => 0,
                'expiryTime'   => 3000000000,
                'duration'     => 3000000000,
                'timeout'      => false,
                'lastActivity' => null,
                'renewable'    => true,
                'data' => [
                    'id' => 'renewal'
                ]
            ],

            // valid session that needs to be renewed, but can't
            '3000000000.nonRenewable' => [
                'startTime'    => 0,
                'expiryTime'   => 3000000000,
                'duration'     => 3000000000,
                'timeout'      => false,
                'lastActivity' => null,
                'renewable'    => false,
                'data' => [
                    'id' => 'nonRenewable'
                ]
            ],

            // valid session that has moved
            '9999999999.moved' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'newSession'   => '9999999999.valid.' . $this->validKey
            ],

            // session that has moved but is expired
            '1000000000.movedExpired' => [
                'startTime'    => 0,
                'expiryTime'   => 1000000000,
                'newSession'   => '9999999999.valid.' . $this->validKey
            ],

            // session that has moved and the new session doesn't exist
            '9999999999.movedInvalid' => [
                'startTime'    => 0,
                'expiryTime'   => 9999999999,
                'newSession'   => '9999999999.doesNotExist.' . $this->invalidKey
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
        if ($this->exists($expiryTime, $id)) {
            $data = $this->sessions[$expiryTime . '.' . $id];

            // validate if this is an actual session or a test data session
            if(is_string($data)) {
                // actual, encrypted session, return as is
                return $data;
            }

            // encrypt the data so that it can be decrypted again
            return Crypto::encrypt(json_encode($data), $this->keyObject, true);
        } else {
            throw new Exception('Session does not exist');
        }
    }

    public function set(int $expiryTime, string $id, string $data)
    {
        if (!$this->exists($expiryTime, $id)) {
            throw new Exception('Session does not exist');
        }

        if (!isset($this->isLocked[$expiryTime . '.' . $id])) {
            throw new Exception('Cannot write to a session that is not locked');
        }

        $this->sessions[$expiryTime . '.' . $id] = $data;
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

class MockSession extends Session {
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
