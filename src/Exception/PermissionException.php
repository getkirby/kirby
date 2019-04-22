<?php

namespace Kirby\Exception;

/**
 * PermissionException
 * Thrown when the current user has insufficient
 * permissions for the action
 *
 * @package   Kirby Exception
 * @author    Nico Hoffmann <nico@getkirby.com>
 * @link      http://getkirby.com
 * @copyright Bastian Allgeier
 * @license   MIT
 */
class PermissionException extends Exception
{
    protected static $defaultKey = 'permission';
    protected static $defaultFallback = 'You are not allowed to do this';
    protected static $defaultHttpCode = 403;
}
