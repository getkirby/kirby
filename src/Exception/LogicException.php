<?php

namespace Kirby\Exception;

/**
 * Thrown for invalid requests that can't work out
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class LogicException extends Exception
{
	protected static string $defaultKey = 'logic';
	protected static string $defaultFallback = 'This task cannot be finished';
	protected static int $defaultHttpCode = 400;
}
