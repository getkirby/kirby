<?php

namespace Kirby\Auth\Exception;

use Kirby\Exception\PermissionException;

/**
 * Thrown when a rate limit has been exceeded
 */
class RateLimitException extends PermissionException
{
	protected static string $defaultKey = 'error.auth.limit';
	protected static string $defaultFallback = 'Rate limit exceeded';
}
