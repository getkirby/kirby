<?php

namespace Kirby\Exception;

/**
 * Thrown when an object could not be created
 * because it already exists
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class DuplicateException extends Exception
{
	protected static string $defaultKey = 'duplicate';
	protected static string $defaultFallback = 'The entry exists';
	protected static int $defaultHttpCode = 400;
}
