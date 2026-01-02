<?php

namespace Kirby\Auth\Exception;

use Kirby\Exception\PermissionException;

/**
 * Thrown when a challenge code verification failed
 */
class InvalidChallengeCodeException extends PermissionException
{
	protected static string $defaultKey = 'access.code';
}
