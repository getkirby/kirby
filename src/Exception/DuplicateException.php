<?php

namespace Kirby\Exception;

/**
 * Thrown when an object could not be created
 * because it already exists
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class DuplicateException extends Exception
{
	protected static string $defaultKey = 'duplicate';
	protected static string $defaultFallback = 'The entry exists';
	protected static int $defaultHttpCode = 400;
}
