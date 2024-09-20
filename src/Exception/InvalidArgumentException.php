<?php

namespace Kirby\Exception;

/**
 * Thrown when a method was called with invalid arguments
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class InvalidArgumentException extends Exception
{
	protected static string $defaultKey = 'invalidArgument';
	protected static string $defaultFallback = 'Invalid argument "{ argument }" in method "{ method }"';
	protected static int $defaultHttpCode = 400;
	protected static array $defaultData = ['argument' => null, 'method' => null];
}
