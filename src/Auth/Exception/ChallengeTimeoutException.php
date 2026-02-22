<?php

namespace Kirby\Auth\Exception;

use Kirby\Exception\PermissionException;

/**
 * Thrown when the challenge's timeout duration has exceeded
 */
class ChallengeTimeoutException extends PermissionException
{
	protected static string $defaultFallback = 'Authentication challenge timeout';
	protected static array $defaultDetails = ['challengeDestroyed' => true];
}
