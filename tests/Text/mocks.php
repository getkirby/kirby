<?php

namespace Kirby\Text;

use Exception;

/**
 * Mock for the PHP error_log() function to ensure reliable testing
 */
function error_log(string $message): bool
{
	if (defined('KIRBY_TESTING') !== true || KIRBY_TESTING !== true) {
		throw new Exception('Mock error_log() function was loaded outside of the test environment. This should never happen.');
	}

	ErrorLog::$log .= $message;

	return true;
}

class ErrorLog
{
	public static string $log = '';
}
