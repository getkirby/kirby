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
	protected static $defaultKey = 'notFound';
	protected static $defaultFallback = 'Not found';
	protected static $defaultHttpCode = 404;
}
