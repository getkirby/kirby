<?php

namespace Kirby\Cms;

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

    return MockTime::$time;
}

class MockTime
{
    public static $time = 1337000000;
}

/**
 * Mock for the PHP usleep() function to skip over
 * waiting times while testing
 *
 * @param int $microSeconds
 * @return void
 */
function usleep(int $microSeconds): void
{
    if (defined('KIRBY_TESTING') !== true || KIRBY_TESTING !== true) {
        throw new Exception('Mock usleep() function was loaded outside of the test environment. This should never happen.');
    }

    // do nothing
}
