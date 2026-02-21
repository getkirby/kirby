<?php

namespace Kirby\Exception;

/**
 * Thrown when something was not found
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class NotFoundException extends Exception
{
	protected static string $defaultKey = 'notFound';
	protected static string $defaultFallback = 'Not found';
	protected static int $defaultHttpCode = 404;
}
