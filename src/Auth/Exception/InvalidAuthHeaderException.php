<?php

namespace Kirby\Auth\Exception;

use Kirby\Exception\InvalidArgumentException;

/**
 * Thrown for an invalid authentication header
 */
class InvalidAuthHeaderException extends InvalidArgumentException
{
	protected static string $defaultFallback = 'Invalid authorization header';
}
