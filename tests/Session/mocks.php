<?php

namespace Kirby\Session;

use Exception;

class InvalidSessionStore
{

}

class TestSessionStore extends SessionStore
{
    public $sessions = [
        '1234567890.abcdefghijabcdefghij' => ''
    ];
    public $isLocked = [];
    public $collectedGarbage = false;

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
            return $this->sessions[$expiryTime . '.' . $id];
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
