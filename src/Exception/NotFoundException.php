<?php

namespace Kirby\Exception;

/**
 * NotFoundException
 * Thrown when something was not found
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class NotFoundException extends Exception
{
	protected static string $defaultKey = 'notFound';
	protected static string $defaultFallback = 'Not found';
	protected static int $defaultHttpCode = 404;
}
