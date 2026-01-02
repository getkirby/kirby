<?php

namespace Kirby\Auth\Exception;

use Kirby\Exception\LogicException;

/**
 * Thrown when no available challenge is found for user
 */
class NoAvailableChallengeException extends LogicException
{
	protected static string $defaultFallback = 'Could not find a suitable authentication challenge';
}
