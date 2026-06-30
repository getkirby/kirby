<?php

namespace Kirby\Cms;

use Exception;
use Kirby\Tests\MockTime;

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

/**
 * Mock for the PHP password_hash() function to reduce the cost
 * while testing
 */
function password_hash(
	string $password,
	string|int|null $algo,
	array $options = []
): string|false {
	\Kirby\Tests\ensureTesting('password_hash');
	$options['cost'] ??= 4;
	return \password_hash($password, $algo, $options);
}

function time(): int
{
	return \Kirby\Tests\time(MockTime::$time);
}
