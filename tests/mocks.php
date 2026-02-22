<?php

namespace Kirby\Tests;

use Exception;

/**
 * Guard to ensure that the mocks are only used
 * within the test environment
 */
function ensureTesting(string $function): void
{
	if (defined('KIRBY_TESTING') !== true || KIRBY_TESTING !== true) {
		throw new Exception(
			'Mock ' . $function . '() function was loaded outside of the test environment. This should never happen.'
		);
	}
}

/**
 * Mock for the PHP time() function to ensure reliable testing
 *
 * @return int A fake timestamp
 */
function time(int $value): int
{
	ensureTesting('time');
	return $value;
}

class MockTime
{
	public static int $time = 1337000000;

	public static function reset(): void
	{
		static::$time = 1337000000;
	}
}

/**
 * Mock for the PHP usleep() function to skip over
 * waiting times while testing
 */
function usleep(): void
{
	ensureTesting('usleep');
	// do nothing
}
