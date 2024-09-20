<?php

namespace Kirby\Exception;

/**
 * Thrown when the current user has insufficient
 * permissions for the action
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class PermissionException extends Exception
{
	protected static string $defaultKey = 'permission';
	protected static string $defaultFallback = 'You are not allowed to do this';
	protected static int $defaultHttpCode = 403;
}
