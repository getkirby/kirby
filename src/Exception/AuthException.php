<?php

namespace Kirby\Exception;

/**
 * AuthException
 * Thrown when authentication is required
 * but no user is logged in.
 *
 * @package   Kirby Exception
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class AuthException extends Exception
{
	protected static $defaultKey = 'auth';
	protected static $defaultFallback = 'Unauthenticated';
	protected static $defaultHttpCode = 401;
}
