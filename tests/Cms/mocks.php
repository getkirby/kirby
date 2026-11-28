<?php

namespace Kirby\Cms;

use Closure;
use Darkroom\Driver;
use Darkroom\Drivers\Gd;
use Darkroom\Format;
use Darkroom\Plan;
use Darkroom\Source;
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

/**
 * Runs a callback before GD makes the thumb,
 * e.g. to let it fail or to look at the files
 */
class CallbackDriver extends Driver
{
	protected Driver $driver;

	public function __construct(
		protected Closure $callback
	) {
		parent::__construct();
		$this->driver = new Gd();
	}

	protected function animates(Format $format): bool
	{
		return $this->driver->canAnimate($format);
	}

	protected function decodes(Format $format): bool
	{
		return $this->driver->canRead($format);
	}

	protected function encodes(Format $format): bool
	{
		return $this->driver->canWrite($format);
	}

	public function icc(Plan $plan): string|null
	{
		return $this->driver->icc($plan);
	}

	public function isAvailable(): bool
	{
		return $this->driver->isAvailable();
	}

	public function orients(Source $source): bool
	{
		return $this->driver->orients($source);
	}

	public function process(Plan $plan, string $file): void
	{
		($this->callback)($plan, $file);
		$this->driver->process($plan, $file);
	}

	protected function reads(Source $source): bool
	{
		return $this->driver->canRead($source);
	}
}
