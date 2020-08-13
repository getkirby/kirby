<?php

namespace Kirby\Cache;

use Exception;

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

    return 1337;
}

class TestCache extends Cache
{
    public $store = [];

    public function set(string $key, $value, int $minutes = 0, int $created = null): bool
    {
        $value = new Value($value, $minutes, $created);
        $this->store[$key] = $value;
        return true;
    }

    public function retrieve(string $key): ?Value
    {
        return $this->store[$key] ?? null;
    }

    public function remove(string $key): bool
    {
        unset($this->store[$key]);
        return true;
    }

    public function flush(): bool
    {
        $this->store = [];
        return true;
    }
}
