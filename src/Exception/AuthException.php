<?php

namespace Kirby\Exception;

/**
 * Thrown when authentication is required
 * but no user is logged in.
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class AuthException extends Exception
{
	protected static string $defaultKey = 'auth';
	protected static string $defaultFallback = 'Unauthenticated';
	protected static int $defaultHttpCode = 401;
}
