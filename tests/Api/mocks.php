<?php

namespace Kirby\Api;

use Exception;

class IniStore
{
	public static array $data = [];
}


/**
 * Mock for the PHP ini_get() function to ensure reliable testing
 */
function ini_get(string $option): string|false
{
	if (defined('KIRBY_TESTING') !== true || KIRBY_TESTING !== true) {
		throw new Exception('Mock ini_get() function was loaded outside of the test environment. This should never happen.');
	}

	return IniStore::$data[$option] ?? \ini_get($option) ?? false;
}

/**
 * Mock for the PHP ini_set() function to ensure reliable testing
 */
function ini_set(string $option, string $value): void
{
	if (defined('KIRBY_TESTING') !== true || KIRBY_TESTING !== true) {
		throw new Exception('Mock ini_set() function was loaded outside of the test environment. This should never happen.');
	}

	IniStore::$data[$option] = $value;
}

/**
 * Mock for the PHP ini_restore() function to ensure reliable testing
 */
function ini_restore(string $option): void
{
	if (defined('KIRBY_TESTING') !== true || KIRBY_TESTING !== true) {
		throw new Exception('Mock ini_restore() function was loaded outside of the test environment. This should never happen.');
	}

	unset(IniStore::$data[$option]);
}
